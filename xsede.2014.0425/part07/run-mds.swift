
import string;
import sys;

import md;

main
{
  int simulations = toint(argv("simulations"));
  int steps = toint(argv("steps"));
  foreach i in [0:simulations-1]
  {
    file out_txt<sprintf("out-%i.txt",i)>;
    file out_trj<sprintf("out-%i.trj",i)>;
    (out_txt, out_trj) = simulate(steps, 10, 3, 0, 10, 2, 1, 0.1, 42);
  }
}
