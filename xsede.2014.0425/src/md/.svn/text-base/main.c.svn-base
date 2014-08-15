
/*
 * main.c
 *
 *  Created on: Aug 13, 2014
 *      Author: wozniak
 */

#include <stdio.h>
#include <stdlib.h>
#include "md.h"


char *outfile = "md.dat";
char *trjfile = "md.trj";


/******************************************************************************/

int main ( int argc, char *argv[] )

/******************************************************************************/
/*
  Purpose:

    MAIN is the main program for MD.

  Discussion:

    MD implements a simple molecular dynamics simulation.

    The velocity Verlet time integration scheme is used.

    The particles interact with a central pair potential.

  Usage:

    md nd np step_num print_step_num dt mass printinfo scale_factor scale_offset seed outFile trajectoryFile
    where
    * nd is the spatial dimension (2 or 3);
    * np is the number of particles (500, for instance);
    * step_num is the number of time steps (500, for instance);
    * print_step_num is the number of snapshot prints (10 for instance);
    * dt is size of timestep;
    * mass is particle mass;
    * printinfo is a string to append to each particle coord
    * scale_offset and scale_factor are used to scale particle positions for logging/rendering (FIXME)
    * seed sets the initial configuration


  Licensing:

    This code is distributed under the GNU LGPL license.

  Modified:

    05 November 2010

  Author:

    Original FORTRAN90 version by Bill Magro.
    C version by John Burkardt.

  Parameters:

    None
*/
{

  double ctime;
  double ctime1;
  double ctime2;
  double dt = 0.0001;
  int i;
  int id;
  double mass = 1.0 * .0001;
  int nd;
  int np;

  int seed = 123456789;
  int step;
  int step_num;
  int step_print;
  int step_print_index = 0;
  int step_print_num = 10;
  double *vel;

  timestamp ( );
  printf ( "\n" );
  printf ( "MD\n" );
  printf ( "  C version\n" );
  printf ( "  A molecular dynamics program.\n" );
/*
  Get the spatial dimension.
*/
  if ( 1 < argc )
  {
    nd = atoi ( argv[1] );
  }
  else
  {
    printf ( "\n" );
    printf ( "  Enter ND, the spatial dimension (2 or 3).\n" );
    scanf ( "%d", &nd );
  }
//
//  Get the number of points.
//
  if ( 2 < argc )
  {
    np = atoi ( argv[2] );
  }
  else
  {
    printf ( "\n" );
    printf ( "  Enter NP, the number of points (500, for instance).\n" );
    scanf ( "%d", &np );
  }
//
//  Get the number of time steps.
//
  if ( 3 < argc )
  {
    step_num = atoi ( argv[3] );
  }
  else
  {
    printf ( "\n" );
    printf ( "  Enter ND, the number of time steps (500 or 1000, for instance).\n" );
    scanf ( "%d", &step_num );
  }
  /*
        Get any additional args (command-line only)
        md nd np step_num [ step__print_num dt mass printinfo scale_factor scale_offset randomseed outfile trjfile ]
  */
  if ( 4 < argc )
  {
    step_print_num = atoi ( argv[4] );
  }
  if ( 5 < argc )
  {
    dt = atof ( argv[5] );
  }
  if ( 6 < argc )
  {
    mass = atof ( argv[6] );
  }
  if ( 7 < argc )
  {
    printinfo = ( argv[7] );
  }
  if ( 8 < argc )
  {
    scale_factor = atof ( argv[8] );
  }
  if ( 9 < argc )
  {
    scale_offset = atof ( argv[9] );
  }
  if ( 10 < argc )
  {
    seed = atof ( argv[10] );
  }
  if ( 11 < argc )
  {
    outfile = argv[11];
  }
  if ( 12 < argc )
  {
    trjfile = argv[12];
  }

/*
  Report.
*/
  printf ( "\n" );
  printf ( "  MD: Argument count: %d\n", argc );
  printf ( "  ND, the spatial dimension, is %d\n", nd );
  printf ( "  NP, the number of particles in the simulation, is %d\n", np );
  printf ( "  STEP_NUM, the number of time steps, is %d\n", step_num );
  printf ( "  STEP_PRINT_NUM, the number of snapshots to print, is %d\n", step_print_num );
  printf ( "  DT, the size of each time step, is %f\n", dt );
  printf ( "  MASS, the particle mass, is %f\n", mass );
  printf ( "  PRINTINFO, the pass-through info to c-ray, is %s\n", printinfo );
  printf ( "  SCALE_FACTOR, the particle position scaling factor, is %f\n", scale_factor );
  printf ( "  SCALE_OFFSET, the particle position scaling offset, is %f\n", scale_offset );
  printf ( "  SEED, the simulation randomization seed, is %d\n", seed );

  ctime1 = cpu_time ( );

  simulate (step_num, step_print_num, step_print, step_print_index,
            np, nd, mass,
            dt, seed, outfile, trjfile);

  ctime2 = cpu_time ( );
  ctime = ctime2 - ctime1;

  printf ( "\n" );
  printf ( "  Elapsed cpu time for main computation:\n" );
  printf ( "  %f seconds.\n", ctime );

#ifdef NOTDEF
  char tarcmd[2000];
  sprintf(tarcmd,"tar zcf %s md??.trj",trjfile);
  system(tarcmd);
#endif

/*
  Terminate.
*/
  printf ( "\n" );
  printf ( "MD\n" );
  printf ( "  Normal end of execution.\n" );

  printf ( "\n" );
  timestamp ( );

  return 0;
}
