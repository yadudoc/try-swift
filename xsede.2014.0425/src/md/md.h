/*
 * md.h
 *
 *  Created on: Aug 13, 2014
 *      Author: wozniak
 */

#ifndef MD_H
#define MD_H

extern double scale_factor, scale_offset;
extern char *printinfo;

double cpu_time ( void );

void timestamp ( void );

void initialize ( int np, int nd, double box[], int *seed, double pos[],
  double vel[], double acc[] );

void compute ( int np, int nd, double pos[], double vel[],
  double mass, double f[], double *pot, double *kin );

void simulate (int step_num, int step_print_num,
               int step_print, int step_print_index,
               int np, int nd,
               double mass,
               double dt,
               int seed,
               char* outfile,
               char* trjfile);

#endif
