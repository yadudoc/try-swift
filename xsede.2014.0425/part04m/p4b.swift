type file;

type kineticInfo {
  int sim;
  float energy;
}

(int result) randomInt ()
{
  float range = 9999999.0;
  float rand = java("java.lang.Math","random");
  string s[] = strsplit(toString(range*rand),"\\.");
  result = toInt(s[0]);
}

app (file out, file traj) simulation (int npart, int steps, int trsnaps, float mass)
{
# mdviz  @out @traj 3 npart steps 50 ".0001" mass "0.1 1.0 0.2 0.05 50.0 0.1" 2.5 2.0 randomInt();
  md     3 npart steps trsnaps ".0001" mass "0.1 1.0 0.2 0.05 50.0 0.1" 2.5 2.0 randomInt() @out @traj;
}

app (file o) analyze (file s[])
{
  mdstats filenames(s) stdout=filename(o);
}

app (file o) kinetic (file s[])
{
  mdmaxk 9999 filenames(s) stdout=filename(o);
}

app (file o) render (file traj, int frame)
{
  renderframe filename(traj) filename(o) frame;
}

app (file o) convert (file s[])
{
  convert "-delay" 20 filenames(s) filename(o);
}

int   nsim   = toInt(arg("nsim","10"));
int   npart  = toInt(arg("npart","50"));
int   steps  = toInt(arg("steps","1000"));
int   trsnaps = 10;
float mass   = toFloat(arg("mass",".005"));

file sim[] <simple_mapper; prefix="output/sim_", suffix=".out">;
file trj[] <simple_mapper; prefix="output/sim_", suffix=".trj">;

foreach i in [0:nsim-1] {
  (sim[i],trj[i]) = simulation(npart,steps,trsnaps,mass);
}

file stats_out<"output/average.out">;
stats_out = analyze(sim);

file ke_out<"output/kinetic.out">;
ke_out = kinetic(sim);

kineticInfo kd[] = readData(ke_out);

tracef("min eK simulation: %i eK: %f\n", kd[0].sim, kd[0].energy);
tracef("max eK simulation: %i eK: %f\n", kd[nsim-1].sim, kd[nsim-1].energy);

file mink[] <simple_mapper; prefix="output/mink/frame_", suffix=".png">;
file maxk[] <simple_mapper; prefix="output/maxk/frame_", suffix=".png">;

foreach i in [0:trsnaps-1] {
  mink[i] = render(trj[kd[0].sim], i);
  maxk[i] = render(trj[kd[nsim-1].sim], i);
}

file minkmovie <"output/mink.gif">;
file maxkmovie <"output/maxk.gif">;

minkmovie = convert(mink);
maxkmovie = convert(maxk);
