<?PHP
   function copy_file($src, $dst) {
      if(!copy($src, $dst)) {
         die("Failed to copy $src to $dst\n");
      }   
   }

   $source = $_POST['source'];
   $web_directory = "/home/tryswift/tryswift";
   $swift_cmd = "nohup /home/tryswift/swift-trunk/cog/modules/swift/dist/swift-svn/bin/swift -sites.file sites.xml -tc.file tc.data -config cf script.swift";

   # Create directory structure
   $unique = uniqid();
   $dirname = $web_directory . "/runs/" . $unique;
   umask(0);
   if (!mkdir($dirname, 0755)) { die("Failed to create folder $dirname"); }
   if (!chdir($dirname))       { die("Unable to chdir to $dirname"); }

   # Copy and create Swift files
   copy_file("$web_directory/config/sites.xml", "$dirname/sites.xml");
   copy_file("$web_directory/config/tc.data", "$dirname/tc.data");
   copy_file("$web_directory/config/cf", "$dirname/cf");
   $script = $dirname . "/script.swift";

   if(!file_put_contents($script, $source)) {
      die("Unable to write swift script");
   }

   # Run Swift
#system("export PATH=/usr/local/jdk1.6.0/bin:$PATH");
   system("echo Swift run starting at $( date +%I:%M:%S ) > $dirname/swift.out");
   system("$swift_cmd 2>&1 | sed -u -e 's/^[ \t]*//' -e s/'Selecting site:'/Ready:/g -e s/'Finished successfully:'/Done:/g >> $dirname/swift.out &");

   print "runs/$unique/swift.out\n";
   print "runs/$unique\n";
?>
