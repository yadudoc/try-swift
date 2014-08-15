<!DOCTYPE html>
<html lang="en">
<head>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
<title>Try Swift</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"> </script>
<!-- <script src="js/tryswift.js"></script> -->
<script src="js/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
<!-- <script src="js/logtail.js" type="text/javascript"></script> -->
<link rel="stylesheet" type="text/css" href="css/tryswift.css">
<script type="text/javascript">
   /* Copyright (c) 2012: Daniel Richman. License: GNU GPL 3 */
   /* Additional features: Priyesh Patel                     */

   function tailf (url, dataelem) {

   var pausetoggle = "#pause";
   var scrollelems = ["pre"];

   var fix_rn = true;
   var load = 30 * 1024; /* 30KB */
   var poll = 1000; /* 1s */

   var kill = false;
   var loading = false;
   var pause = false;
   var reverse = false;
   var log_data = "";
   var log_size = 0;

   function get_log() {
     if (kill | loading) return;
     loading = true;

     var range;
     if (log_size === 0)
       /* Get the last 'load' bytes */
       range = "-" + load.toString();
    else
      /* Get the (log_size - 1)th byte, onwards. */
      range = (log_size - 1).toString() + "-";

     /* The "log_size - 1" deliberately reloads the last byte, which we already
      * have. This is to prevent a 416 "Range unsatisfiable" error: a response
      * of length 1 tells us that the file hasn't changed yet. A 416 shows that
      * the file has been trucnated */

     $.ajax(url, {
       dataType: "text",
	   cache: false,
	   headers: {Range: "bytes=" + range},
	   success: function (data, s, xhr) {
	   loading = false;

	   var size;

	   if (xhr.status === 206) {
	     if (data.length > load)
	       throw "Expected 206 Partial Content";

	     var c_r = xhr.getResponseHeader("Content-Range");
	     if (!c_r)
	       throw "Server did not respond with a Content-Range";

	     size = parseInt(c_r.split("/")[1]);
	     if (isNaN(size))
	       throw "Invalid Content-Range size";
	   } else if (xhr.status === 200) {
	     if (log_size > 1)
	       throw "Expected 206 Partial Content";

	     size = data.length;
	   }

	   var added = false;

	   if (log_size === 0) {
	     /* Clip leading part-line if not the whole file */
	     if (data.length < size) {
	       var start = data.indexOf("\n");
	       log_data = data.substring(start + 1);
	     } else {
	       log_data = data;
	     }

	     added = true;
	   } else {
	     /* Drop the first byte (see above) */
	     log_data += data.substring(1);

	     if (log_data.length > load) {
	       var start = log_data.indexOf("\n", log_data.length - load);
	       log_data = log_data.substring(start + 1);
	     }

	     if (data.length > 1)
	       added = true;
	   }

	   log_size = size;
	   if (added)
	     show_log(added);
	   setTimeout(get_log, poll);
	 },
	   error: function (xhr, s, t) {
	   loading = false;

	   if (xhr.status === 416 || xhr.status == 404) {
	     /* 416: Requested range not satisfiable: log was truncated. */
	     /* 404: Retry soon, I guess */

	     log_size = 0;
	     log_data = "";
	     show_log();

	     setTimeout(get_log, poll);
	   } else {
	     if (s == "error")
	       error(xhr.statusText);
                else
		  error("AJAX Error: " + s);
	   }
	 }
       });
   }

   function scroll(where) {
     $("#output").animate({ scrollTop: $('#output')[0].scrollHeight}, 'slow');
   }

   function show_log() {
     if (pause) return;

     var t = log_data;

     if (reverse) {
       var t_a = t.split(/\n/g);
       t_a.reverse();
       if (t_a[0] == "") 
	 t_a.shift();
       t = t_a.join("\n");
     }

     if (fix_rn)
       t = t.replace(/\n/g, "\r\n");

     $(dataelem).text(t);
     scroll(0);
   }

   function error(what) {
     kill = true;

     $(dataelem).text("An error occurred :-(.\r\n" +
                     "Reloading may help; no promises.\r\n" + 
		      what);
     scroll(0);
   }

   $(document).ready(function () {
       $(window).error(error);

       /* If URL is /logtail/?noreverse display in chronological order */
       var hash = location.search.replace(/^\?/, "");
       if (hash == "noreverse")
	 reverse = false;

       /* Add pause toggle */
       $(pausetoggle).click(function (e) {
	   pause = !pause;
	   $(pausetoggle).text(pause ? "Unpause" : "Pause");
	   show_log();
	   e.preventDefault();
	 });
       
       get_log();
     });
   
 }
</script>

</head>

<body>

<div id="wrapright">

<div id="tutorial">


<div class="example hidden" id="page-1">

<iframe src="scripts/page-1.html" style="border-style: none; width: 100%; height: 1600px;"></iframe>

<textarea id="source-1">
tracef("Hello, World! This is my %s code!\n","Swift");</textarea>

</div>  <!-- example -->

<div class="example hidden" id="page-2">

<iframe src="scripts/page-2.html" style="border-style: none; width: 100%; height: 1600px"></iframe>

<textarea id="source-2">
type file;

app (file out) echo_app (string s)
{
   echo s stdout=filename(out);
}

file out <"out.txt">;
out = echo_app("Hello world!");
</textarea>

</div>

<div class="example hidden" id="page-3">

<iframe src="scripts/page-3.html" style="border-style: none; width: 100%; height: 1600px"></iframe>

<textarea id="source-3">
type file;

app (file o) simulate_app ()
{
  simulate stdout=filename(o);
}

foreach i in [1:10] {
  string fname=strcat("output/sim_", i, ".out");
  file f <single_file_mapper; file=fname>;
  f = simulate_app();
}
</textarea>


</div>


<div class="example hidden" id="page-4">

<iframe src="scripts/page-4.html" style="border-style: none; width: 100%; height: 1600px"></iframe>

<textarea id="source-4">
type file;

app (file o) simulate_app (int time)
{
  simulate "-t" time stdout=filename(o);
}

app (file o) stats_app (file s[])
{
  stats filenames(s) stdout=filename(o);
}

file sims[];
int time = 5;
int nsims = 10;

foreach i in [1:nsims] {
  string fname = strcat("output/sim_",i,".out");
  file simout <single_file_mapper; file=fname>;
  simout = simulate_app(time);
  sims[i] = simout;
}

file average <"output/average.out">;
average = stats_app(sims);
</textarea>

</div>

<div class="example hidden" id="page-5">

<iframe src="scripts/page-5.html" style="border-style: none; width: 100%; height: 1600px"></iframe>

<textarea id="source-5">
type file;

app (file out) genseed_app (int nseeds)
{
  genseed "-r" 2000000 "-n" nseeds stdout=@out;
}

app (file out) genbias_app (int bias_range, int nvalues)
{
  genbias "-r" bias_range "-n" nvalues stdout=@out;
}

app (file out, file log) simulation_app (int timesteps, int sim_range,
    file bias_file, int scale, int sim_count)
{
  simulate "-t" timesteps "-r" sim_range "-B" @bias_file "-x" scale
           "-n" sim_count stdout=@out stderr=@log;
}

app (file out, file log) analyze_app (file s[])
{
  stats filenames(s) stdout=@out stderr=@log;
}

# Values that shape the run
int nsim = 10;   # number of simulation programs to run
int steps = 1;   # number of timesteps (seconds) per simulation
int range = 100; # range of the generated random numbers
int values = 10; # number of values generated per simulation

# Main script and data
tracef("\n*** Script parameters: nsim=%i range=%i num values=%i\n\n", 
		nsim, range, values);

# Dynamically generated bias for simulation ensemble
file seedfile<"output/seed.dat">;
seedfile = genseed_app(1);

int seedval = readData(seedfile);
tracef("Generated seed=%i\n", seedval);

file sims[]; # Array of files to hold each simulation output

foreach i in [0:nsim-1] {
  file biasfile <single_file_mapper; 
  				 file=strcat("output/bias_",i,".dat")>;
  file simout   <single_file_mapper; 
  				 file=strcat("output/sim_",i,".out")>;
  file simlog   <single_file_mapper; 
  				 file=strcat("output/sim_",i,".log")>;
  biasfile = genbias_app(1000, 20);
  (simout,simlog) = simulation_app(steps, range, biasfile, 
  								   1000000, values);
  sims[i] = simout;
}

file stats_out<"output/average.out">;
file stats_log<"output/average.log">;
(stats_out,stats_log) = analyze_app(sims);
</textarea>

</div>

<div class="example hidden" id="page-6">

<iframe src="scripts/page-6.html" style="border-style: none; width: 100%; height: 1600px"></iframe>

<textarea id="source-6">
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
  md 3 npart steps trsnaps ".0001" mass "0.1 1.0 0.2 0.05 50.0 0.1" 2.5 2.0 randomInt() @out @traj;
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
</textarea>

</div>
		</div> <!-- tutorial -->

	</div> <!-- wrapright -->


		<div id="workarea">

			<div id="menu">

			</div>

		
				<div id="workarea-top">
				<div id="editor"></div> 
				</div>


			<div id="workarea-bottom">
			<div id="bottom-top">
			<div id="leftButtons">
				<button id="executeButton">Execute</button>
				<button id="resetButton">Reset</button>
				<select id="outputs">
					<option value="files">File outputs</option>
				</select>
  <script type="text/javascript">
  $('#outputs').hide();
  </script>
			</div>
			<div id="rightButtons">
				<button id="previousButton" disabled="true"> <b>&#60;<b> </button>
				<select id="topics">
					<option>Introduction</option>
					<option>Hello World!</option>
					<option>Foreach</option>
					<option>Multiple apps</option>
					<option>Multi-stage workflows</option>
					<option>Particle simulation</option>
				</select>
				<button id="nextButton"> <b>&#62;<b> </button>
			</div> <!-- buttons -->
		</div>
		<div id="bottom-bottom">
			<div id="output">
				<pre id="swiftOutput"></pre>

			</div> <!-- output -->
		</div>
	</div>

</div>


</body>

<script src="js/tryswift.js"></script>


</html>

























