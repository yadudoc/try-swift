# include <assert.h>
# include <stdlib.h>
# include <stdio.h>
# include <time.h>
# include <math.h>

#include "md.h"

double scale_factor = 2.5, scale_offset = -2.0;
char *printinfo = "0.05 1.0 0.2 0.05 50.0 0.1";


double dist ( int nd, double r1[], double r2[], double dr[] );

double r8_uniform_01 ( int *seed );
void update ( int np, int nd, double pos[], double vel[], double f[],
  double acc[], double mass, double dt );
void snap ( int np, int nd, int t, double pos[], double vel[], double f[],
  double acc[], double mass, double dt );

static void trj_file_open(char *trjfile);
static void trj_file_close(void);

void simulate (int step_num, int step_print_num,
               int step_print, int step_print_index,
               int np, int nd,
               double mass,
               double dt,
               int seed,
               char* outfile,
               char* trjfile)
{
  /*
    Allocate memory.
  */
    double* acc = ( double * ) malloc ( nd * np * sizeof ( double ) );
    double* box = ( double * ) malloc ( nd * sizeof ( double ) );
    double* force = ( double * ) malloc ( nd * np * sizeof ( double ) );
    double* pos = ( double * ) malloc ( nd * np * sizeof ( double ) );
    double* vel = ( double * ) malloc ( nd * np * sizeof ( double ) );
  /*
    Set the dimensions of the box.
  */
    for (int i = 0; i < nd; i++ )
    {
      box[i] = 10.0;
    }

    printf ( "\n" );
    printf ( "  Initializing positions, velocities, and accelerations.\n" );

    trj_file_open(trjfile);

  /*
    Set initial positions, velocities, and accelerations.
  */
    initialize ( np, nd, box, &seed, pos, vel, acc );
  /*
    Compute the forces and energies.
  */
    printf ( "\n" );
    printf ( "  Computing initial forces and energies.\n" );

    double potential, kinetic;
    compute ( np, nd, pos, vel, mass, force, &potential, &kinetic );
    double e0 = potential + kinetic;
  /*
    This is the main time stepping loop:
      Compute forces and energies,
      Update positions, velocities, accelerations.
  */
    printf ( "\n" );
    printf ( "  At each step, we report the potential and kinetic energies.\n" );
    printf ( "  The sum of these energies should be a constant.\n" );
    printf ( "  As an accuracy check, we also print the relative error\n" );
    printf ( "  in the total energy.\n" );
    printf ( "\n" );
    printf ( "      Step      Potential       Kinetic        (P+K-E0)/E0\n" );
    printf ( "                Energy P        Energy K       Relative Energy Error\n" );
    printf ( "\n" );

    FILE *ofile = fopen(outfile,"w");
    fprintf (ofile, "      Step      Potential       Kinetic        RelativeErr\n" );

    int step = 0;
    printf ( "  %8d  %14f  %14f  %14e\n",
      step, potential, kinetic, ( potential + kinetic - e0 ) / e0 );
    fprintf ( ofile, "  %8d  %14f  %14f  %14e\n",
      step, potential, kinetic, ( potential + kinetic - e0 ) / e0 );
    step_print_index = step_print_index + 1;
    step_print = ( step_print_index * step_num ) / step_print_num;


  for (step = 1; step <= step_num; step++ )
  {
    compute ( np, nd, pos, vel, mass, force, &potential, &kinetic );

    if ( step == step_print )
    {
      printf ( "  %8d  %14f  %14f  %14e\n", step, potential, kinetic,
               ( potential + kinetic - e0 ) / e0 );
      fprintf ( ofile, "  %8d  %14f  %14f  %14e\n", step, potential, kinetic,
                ( potential + kinetic - e0 ) / e0 );
      step_print_index = step_print_index + 1;
      step_print = ( step_print_index * step_num ) / step_print_num;
      snap (np, nd, step, pos, vel, force, acc, mass, dt );
    }
    update ( np, nd, pos, vel, force, acc, mass, dt );
  }

  free ( acc );
  free ( box );
  free ( force );
  free ( pos );
  free ( vel );

  fclose(ofile);
  trj_file_close();
}

/******************************************************************************/

void compute ( int np, int nd, double pos[], double vel[], 
  double mass, double f[], double *pot, double *kin )

/******************************************************************************/
/*
  Purpose:

    COMPUTE computes the forces and energies.

  Discussion:

    The computation of forces and energies is fully parallel.

    The potential function V(X) is a harmonic well which smoothly
    saturates to a maximum value at PI/2:

      v(x) = ( sin ( min ( x, PI2 ) ) )^2

    The derivative of the potential is:

      dv(x) = 2.0 * sin ( min ( x, PI2 ) ) * cos ( min ( x, PI2 ) )
            = sin ( 2.0 * min ( x, PI2 ) )

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    21 November 2007

  Author:

    Original FORTRAN90 version by Bill Magro.
    C version by John Burkardt.

  Parameters:

    Input, int NP, the number of particles.

    Input, int ND, the number of spatial dimensions.

    Input, double POS[ND*NP], the position of each particle.

    Input, double VEL[ND*NP], the velocity of each particle.

    Input, double MASS, the mass of each particle.

    Output, double F[ND*NP], the forces.

    Output, double *POT, the total potential energy.

    Output, double *KIN, the total kinetic energy.
*/
{
  double d;
  double d2;
  int i;
  int j;
  int k;
  double ke;
  double pe;
  double PI2 = 3.141592653589793 / 2.0;
  double rij[3];

  pe = 0.0;
  ke = 0.0;

  for ( k = 0; k < np; k++ )
  {
/*
  Compute the potential energy and forces.
*/
    for ( i = 0; i < nd; i++ )
    {
      f[i+k*nd] = 0.0;
    }

    for ( j = 0; j < np; j++ )
    {
      if ( k != j )
      {
        d = dist ( nd, pos+k*nd, pos+j*nd, rij );
/*
  Attribute half of the potential energy to particle J.
*/
        if ( d < PI2 )
        {
          d2 = d;
        }
        else
        {
          d2 = PI2;
        }

        pe = pe + 0.5 * pow ( sin ( d2 ), 2 );

        for ( i = 0; i < nd; i++ )
        {
          f[i+k*nd] = f[i+k*nd] - rij[i] * sin ( 2.0 * d2 ) / d;
        }
      }
    }
/*
  Compute the kinetic energy.
*/
    for ( i = 0; i < nd; i++ )
    {
      ke = ke + vel[i+k*nd] * vel[i+k*nd];
    }
  }

  ke = ke * 0.5 * mass;
  
  *pot = pe;
  *kin = ke;

  return;
}
/*******************************************************************************/

double cpu_time ( void )

/*******************************************************************************/
/*
  Purpose:
 
    CPU_TIME reports the total CPU time for a program.

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    27 September 2005

  Author:

    John Burkardt

  Parameters:

    Output, double CPU_TIME, the current total elapsed CPU time in second.
*/
{
  double value;

  value = ( double ) clock ( ) / ( double ) CLOCKS_PER_SEC;

  return value;
}
/******************************************************************************/

double dist ( int nd, double r1[], double r2[], double dr[] )

/******************************************************************************/
/*
  Purpose:

    DIST computes the displacement (and its norm) between two particles.

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    21 November 2007

  Author:

    Original FORTRAN90 version by Bill Magro.
    C version by John Burkardt.

  Parameters:

    Input, int ND, the number of spatial dimensions.

    Input, double R1[ND], R2[ND], the positions of the particles.

    Output, double DR[ND], the displacement vector.

    Output, double D, the Euclidean norm of the displacement.
*/
{
  double d;
  int i;

  d = 0.0;
  for ( i = 0; i < nd; i++ )
  {
    dr[i] = r1[i] - r2[i];
    d = d + dr[i] * dr[i];
  }
  d = sqrt ( d );

  return d;
}
/******************************************************************************/

void initialize ( int np, int nd, double box[], int *seed, double pos[], 
  double vel[], double acc[] )

/******************************************************************************/
/*
  Purpose:

    INITIALIZE initializes the positions, velocities, and accelerations.

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    20 July 2008

  Author:

    Original FORTRAN90 version by Bill Magro.
    C version by John Burkardt.

  Parameters:

    Input, int NP, the number of particles.

    Input, int ND, the number of spatial dimensions.

    Input, double BOX[ND], specifies the maximum position
    of particles in each dimension.

    Input, int *SEED, a seed for the random number generator.

    Output, double POS[ND*NP], the position of each particle.

    Output, double VEL[ND*NP], the velocity of each particle.

    Output, double ACC[ND*NP], the acceleration of each particle.
*/
{
  int i;
  int j;
/*
  Give the particles random positions within the box.
*/
  for ( j = 0; j < np; j++ )
  {
    for ( i = 0; i < nd; i++ )
    {
      pos[i+j*nd] = box[i] * r8_uniform_01 ( seed );
      vel[i+j*nd] = 0.0;
      acc[i+j*nd] = 0.0;
    }
  }
  return;
}
/******************************************************************************/

double r8_uniform_01 ( int *seed )

/******************************************************************************/
/*
  Purpose:

    R8_UNIFORM_01 is a unit pseudorandom R8.

  Discussion:

    This routine implements the recursion

      seed = 16807 * seed mod ( 2^31 - 1 )
      unif = seed / ( 2^31 - 1 )

    The integer arithmetic never requires more than 32 bits,
    including a sign bit.

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    11 August 2004

  Author:

    John Burkardt

  Reference:

    Paul Bratley, Bennett Fox, Linus Schrage,
    A Guide to Simulation,
    Springer Verlag, pages 201-202, 1983.

    Bennett Fox,
    Algorithm 647:
    Implementation and Relative Efficiency of Quasirandom
    Sequence Generators,
    ACM Transactions on Mathematical Software,
    Volume 12, Number 4, pages 362-376, 1986.

  Parameters:

    Input/output, int *SEED, a seed for the random number generator.

    Output, double R8_UNIFORM_01, a new pseudorandom variate, strictly between
    0 and 1.
*/
{
  int k;
  double r;

  k = *seed / 127773;

  *seed = 16807 * ( *seed - k * 127773 ) - k * 2836;

  if ( *seed < 0 )
  {
    *seed = *seed + 2147483647;
  }

  r = ( double ) ( *seed ) * 4.656612875E-10;

  return r;
}
/******************************************************************************/

void timestamp ( void )

/******************************************************************************/
/*
  Purpose:

    TIMESTAMP prints the current YMDHMS date as a time stamp.

  Example:

    31 May 2001 09:45:54 AM

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    24 September 2003

  Author:

    John Burkardt

  Parameters:

    None
*/
{
# define TIME_SIZE 40

  static char time_buffer[TIME_SIZE];
  const struct tm *tm;
  size_t len;
  time_t now;

  now = time ( NULL );
  tm = localtime ( &now );

  len = strftime ( time_buffer, TIME_SIZE, "%d %B %Y %I:%M:%S %p", tm );

  printf ( "%s\n", time_buffer );

  return;
# undef TIME_SIZE
}
/******************************************************************************/

void update ( int np, int nd, double pos[], double vel[], double f[], 
  double acc[], double mass, double dt )

/******************************************************************************/
/*
  Purpose:

    UPDATE updates positions, velocities and accelerations.

  Discussion:

    The time integration is fully parallel.

    A velocity Verlet algorithm is used for the updating.

    x(t+dt) = x(t) + v(t) * dt + 0.5 * a(t) * dt * dt
    v(t+dt) = v(t) + 0.5 * ( a(t) + a(t+dt) ) * dt
    a(t+dt) = f(t) / m

  Licensing:

    This code is distributed under the GNU LGPL license. 

  Modified:

    21 November 2007

  Author:

    Original FORTRAN90 version by Bill Magro.
    C version by John Burkardt.

  Parameters:

    Input, int NP, the number of particles.

    Input, int ND, the number of spatial dimensions.

    Input/output, double POS[ND*NP], the position of each particle.

    Input/output, double VEL[ND*NP], the velocity of each particle.

    Input, double F[ND*NP], the force on each particle.

    Input/output, double ACC[ND*NP], the acceleration of each particle.

    Input, double MASS, the mass of each particle.

    Input, double DT, the time step.
*/
{
  int i;
  int j;
  double rmass;

  rmass = 1.0 / mass;

  for ( j = 0; j < np; j++ )
  {
    for ( i = 0; i < nd; i++ )
    {
      pos[i+j*nd] = pos[i+j*nd] + vel[i+j*nd] * dt + 0.5 * acc[i+j*nd] * dt * dt;
      vel[i+j*nd] = vel[i+j*nd] + 0.5 * dt * ( f[i+j*nd] * rmass + acc[i+j*nd] );
      acc[i+j*nd] = f[i+j*nd] * rmass;
    }
	/*
s	-0.5 -0.3 -1	0.05	1.0 0.2 0.05		50.0	0.1
s	0.5 -0.4 0		0.05	0.1 0.85 1.0		50.0	0.1
	*/
    char *s = "0.05 1.0 0.2 0.05 50.0 0.1";
    /* printf("s %f %f %f %s\n", pos[0+j*nd]/20.0, pos[1+j*nd]/20.0, pos[2+j*nd]/20.0, s); */
  }

  return;
}


double scale (double x)
{
  return( (x / scale_factor) - scale_offset);
  /* return( (x / 2.5) - 2.0); */
}

static int snapid = 0;
static FILE *trj_file = NULL;

static void trj_file_open(char *trjfile)
{
#ifdef NOTDEF
  char snapfile[100];
  sprintf(snapfile, "md%02d.trj", snapid);
#endif
  trj_file = fopen(trjfile, "w");
  assert(trj_file != NULL);
}

static void trj_file_close()
{
  fclose(trj_file);
  trj_file = NULL;
}

void snap ( int np, int nd, int t, double pos[], double vel[], double f[],
  double acc[], double mass, double dt )
{
  int j;

  for ( j = 0; j < np; j++ )
  {
    fprintf(trj_file, "%d s %f %f %f %s\n",
            snapid, scale(pos[0+j*nd]), scale(pos[1+j*nd]), scale(pos[2+j*nd]), printinfo);
  }

  snapid++;
}
