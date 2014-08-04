<?PHP
   function copy_file($src, $dst) {
      if(!copy($src, $dst)) {
         die("Failed to copy $src to $dst\n");
      }   
   }

   // var_dump($_POST["source"]);
   $source = $_POST['source'];
   $web_directory = "/Applications/MAMP/htdocs/try-swift";
   $swift_cmd = "/Applications/MAMP/htdocs/try-swift/swift-0.95-RC6/bin/swift -config swift.properties script.swift -site=local";

   # Create directory structure
   $unique = uniqid();
   $dirname = $web_directory . "/runs/" . $unique;
   umask(0);
   if (!mkdir($dirname, 0755)) { die("Failed to create folder $dirname"); }
   if (!chdir($dirname))       { die("Unable to chdir to $dirname"); }

   # Copy and create Swift files
   copy_file("$web_directory/config/swift.properties", "$dirname/swift.properties");
   $script = $dirname . "/script.swift";

   if(!file_put_contents($script, $source)) {
      die("Unable to write swift script");
   }

   # Run Swift
   system("echo Swift run starting at $( date +%I:%M:%S ) > $dirname/swift.out");
   system("which java >> $dirname/swift.out ");
   system("$swift_cmd -version >> $dirname/swift.out");
   // system("$swift_cmd 2>&1 | sed -u -e 's/^[ \t]*//' -e s/'Selecting site:'/Ready:/g -e s/'Finished successfully:'/Done:/g >> $dirname/swift.out &");

   print "runs/$unique/swift.out\n";
   print "runs/$unique\n";
?>