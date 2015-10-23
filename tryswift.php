<?PHP
   function copy_file($src, $dst) {
      if(!copy($src, $dst)) {
         die("Failed to copy $src to $dst\n");
      }   
   }

   $source = $_POST['source'];
   $web_directory = "/var/www/html";
   $swift_cmd = "nohup /var/www/html/swift-0.96.2/bin/swift script.swift";

   # Create directory structure
   $unique = uniqid();
   $dirname = $web_directory . "/runs/" . $unique;
   umask(0);
   if (!mkdir($dirname, 0755)) { die("Failed to create folder $dirname"); }
   if (!chdir($dirname))       { die("Unable to chdir to $dirname"); }

   # Copy and create Swift files
   #copy_file("$web_directory/config/sites.xml", "$dirname/sites.xml");
   #copy_file("$web_directory/config/tc.data", "$dirname/tc.data");
   #copy_file("$web_directory/config/cf", "$dirname/cf");
   copy_file("$web_directory/config/swift.conf", "$dirname/swift.conf");
   $script = $dirname . "/script.swift";

   if(!file_put_contents($script, $source)) {
      die("Unable to write swift script");
   }

   # Run Swift
   system("export PATH=/usr/local/bin/jdk1.7.0_51/bin:$PATH");
   system("export TRYSWIFTROOT=/var/www/html");
   system("echo Swift run starting at $( date +%I:%M:%S ) > $dirname/swift.out");
   system("$swift_cmd 2>&1 | sed -u -e 's/^[ \t]*//' -e s/'Selecting site:'/Ready:/g -e s/'Finished successfully:'/Done:/g >> $dirname/swift.out &");

   print "runs/$unique/swift.out\n";
   print "runs/$unique\n";
?>
