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

<div id="header">
<h1>Welcome to Try Swift!</h1>
</div>

<div id="tutorial">


<div class="example hidden" id="page-1">
<h2>Introduction</h2>
<p>
	This web interface lets you learn Swift from your browser - nothing to install.
</p>


<p>
	You can run the built-in example scripts, change them, and run your own scripts.
</p>


<ul>

	<li>select a code example from the drop-down "Example" menu above</li>
	<li>click &#91;Explain&#93; to view an explanation of the  example</li>
	<li>click [Execute] (below) to run the example</li>
	<li>click [File outputs] to view the output files produced by your script</li>
	<li>click [Reset] to restore the example to its initial state (LOSES YOUR CHANGES!)</li>

</ul>


<p>The example scripts are:</p>

<ol type="i">

	<li><b>Hello World</b> - Shows the basic syntax for running an app to produce a file</li>
	<li><b>Foreach</b> - Introduces the foreach statement to run multiple apps in parallel</li>
	<li><b>Multiple apps</b> - Show dependencies between apps to specify a workflow</li>
	<li><b>Multi-stage</b> - Example of a larger multi-stage workflow</li>

</ol>

<p>To test your own script, enter some Swift code below, or in any other window.</p>

<p>TrySwift gives you a few built-in "apps" to run, as needed by each example.
	It executes on a pool of virtual machines, one VM per Swift run (at the moment).</p>

	<p>The Swift language is explained in the <a href="http://swift-lang.org/docs" target="_blank">Swift User Guide</a></p>


<textarea id="source-1">
tracef("Hello, World! This is my %s code!\n","Swift");</textarea>

		<!-- <div class="script">

		<pre>tracef("Hello, World! This is my %s code!\n","Swift");</pre>

	</div> -->


</div>  <!-- example -->

<div class="example hidden" id="page-2">
	<h2>Hello World!</h2>


	<p>Let's take a closer look at this script and go through it line by line.</p>
	<pre>type file;</pre>
	<p>Most Swift scripts will make extensive use of files. This line allows us to use the file datatype and will be the first line of most scripts.</p>
	<pre>
app (file out) echo_app (string s)
{
  echo s stdout=filename(out);
} 
</pre>
<p>The code above defines an application. In this case, the app is the unix utility echo. Every app function can define an input and an output. Outputs are defined to the left of the app name (file o). Inputs are defined to the right of the app name (string s).</p>
<pre>echo s stdout=filename(o);</pre>
<p>The echo command by itself does not create any files. Instead, what we do in this example is redirect the output of the command to a file by using <pre>stdout=filename(o)</pre></p>
<p>Now that our app function is defined, let's create a file and call echo.</p>
<pre>
file out <"out.txt">;
out = echo("Hello world!");
</pre>
<p>Try running the program. When it is finished, you should see a file called out.txt get created that contains the text "Hello world!"</p>
<h3>Exercises</h3>
<ul>
	<li>Change the text from "Hello world!" to "Hello Swift!"</li>
	<li>Change the name of the output file</li>
</ul>

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
	<h2>Foreach</h2>


	<p>In this example, we first change our application. Instead of using "echo", we use an app called simulate. The simulate application serves as a trivial proxy for any more complex scientific simulation application. In this example, simulate will print a single number in the range of 1-100.</p>

	<pre>
app (file o) simulate_app ()
{
  simulate stdout=filename(o);
}</pre>

	<p>Swift is a simple scripting language for executing many instances of ordinary application programs on distributed parallel resources. Swift scripts run many copies of ordinary programs concurrently, using statements like this:</p>

	<pre>
foreach i in [1:10] 
{
  string fname=strcat("output/sim_", i, ".out");
  file f ;
  f = simulate_app();
}</pre>

	<p>In this example the foreach loop will iterate over a range of numbers from 0 to 9. It will then map an output file and run the simulate app. The important thing to realize about this example is that every iteration will run in parallel (up to a user defined throttle).</p>

	<p>Another new feature in this example is the file mapper. In the first hello world example, we saw a simplified version of a file mapper with the line:</p>

	<pre>
file out <"out.txt">;</pre>

	<p>This is the same as saying</p>

	<pre>file out <single_file_mapper; file="output.txt">;</pre>

	<p>Defining a file mapper in this way allows a file name to be the content of a string or the output of another function. The strcat function combines multiple strings into a single string.</p>

	<p>Execute the script and examine the results. You should see 10 files creates with random numbers.</p>

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
	<h2>Multiple apps</h2>
	<p>After all the parallel simulations in an ensemble run have completed, it is typically necessary to gather and analyze their results with some kind of post-processing analysis program or script. This script shows an example of this.</p>

	<p>The first change in this script is to the simulate script:</p>

	<pre>
app (file o) simulate_app (int time)
{
  simulate "-t" time stdout=filename(o);
}</pre>

	<p>Simulate now takes an argument, time. The command "simulate -t 10" will sleep for 10 seconds before printing a value. This is an example of how to pass command line arguments to an app in Swift.</p>

	<p>We introduce a new app call called stats:</p>

	<pre>
app (file o) stats_app (file s[])
{
  stats filenames(s) stdout=filename(o);
}</pre>

	<p>The stats app function takes an array of files as input (file s[]). The stats app takes a list of files, reads the numbers contained inside, and prints the average value. The filenames() function simply prints a list of all filenames contained within a file array.
	</p>

	<p>Within the foreach loop:</p>

	<pre>
simout = simulate_app(time);
sims[i] = simout;</pre>

		<p>We now add each simout file to the sims array before finally passing all the files to stats</p>

		<pre>
foreach i in [1:nsims] {
  string fname = strcat("output/sim_",i,".out");
  file simout <single_file_mapper; file=fname>
  simout = simulate_app(time);
  sims[i] = simout;
}

file average <"output/average.out">
average = stats_app(sims);</pre>

		<p>Execute the script and view output/average.out to verify it succeeded.</p>

		<h3>Exercises</h3>
		<ul>

			<li>Modify the simulate_app function so that it accepts a second int that will representing range (hint: multiple inputs are separated by commas).</li>

			<li>Modify the simulate command line arguments. The current arguments are "-t time". Simulate takes another command line option, -r. The -r option sets the range of random numbers it generates. Call simulate with the added options -r 1000.</li>

		</ul>

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
		<h2>Multi-stage workflows</h2>

		<p>This example expands the workflow pattern of the previous example by adding additional stages to the workflow. Here, we generate a dynamic seed value that will be used by all of the simulations, and for each simulation, we run an pre-processing application to generate a unique "bias file". This pattern is shown below, followed by the Swift script.</p>

		<img src="scripts/005-multistage.png" style="display: block; margin-left: auto; margin-right: auto;"></img>

		<p>Note that the workflow is based on data flow dependencies: each simulation depends on the seed value, calculated in these two dependent statements:</p>

		<pre>
seedfile = genseed_app(1);
int seedval = readData(seedfile);</pre>

			<p>The workflow also depends on the bias file, computed and then consumed in these two dependent statements:</p>

			<pre>
biasfile = genbias_app(1000, 20);
(simout,simlog) = simulation_app(steps, range, biasfile, 
1000000, values);</pre>

				<p>We produce 20 values in each bias file. Simulations of less than 20 values ignore the unneeded numbers, while simualtions of more than 20 will use the last bias number for all values past 20.</p>

				<h3>Exercises</h3>
				<ul>

					<li>Adjust the code to produce the same number of bias values as is needed for each simulation.</li>

					<li>Modify the script to generate a unique seed value for each simulation</li>

				</ul>

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

		</div> <!-- tutorial -->

	</div> <!-- wrapright -->


		<div id="workarea">

			<div id="menu">

			</div>

		
			<!-- <div id="code"> -->

				<div id="workarea-top">
				<div id="editor"></div> 
				</div>

				<!-- <script src="js/src-min/ace.js" type="text/javascript" charset="utf-8"></script>

				// <script>
				// var editor = ace.edit("editor");
				// editor.setFontSize('12px');
				// editor.setTheme("ace/theme/KatzenMilch");
				// editor.getSession().setMode("ace/mode/text");
				// editor.setValue($("#source-1").text(), -1);

				// </script>
 -->

<!-- 			</div> -->  <!-- code  -->

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
				<button id="previousButton" disabled="true">Previous</button>
				<button id="nextButton">Next</button>
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

























