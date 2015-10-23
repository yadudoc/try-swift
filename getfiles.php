<?PHP
$dir = $_POST['dir'];
$web_directory = "/var/www/html";
chdir("$web_directory/$dir");
system("$web_directory/waitswift.sh");
# Sorted, recursive list of all files
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$web_directory/$dir"));
$paths = array();
while($iterator->valid()) {
  if($iterator->isFile()) {
     $paths[] = $iterator->getSubPathname();
   }
   $iterator->next();
}
natsort($paths);

foreach ($paths as $file) {
      if(  $file != "err.txt" &&
           $file != "swift.conf" &&
           $file != "script.swift" &&
           $file != "swift.out" &&
           $file != "swift.log" &&
           $file != "swift.pid" &&
           substr($file, -2) != ".d" &&
           substr($file, -4) != ".kml" &&
           substr($file, -7) != ".swiftx" &&
           substr($file, -4) != ".log"
	) {
           $size = filesize($file);
           #$type = shell_exec("file -b $file");
           # print "<div class=\"span2\"><a href=\"$dir/$file\" target=\"_blank\">$file</a> [$size bytes]</div>\n";
           print "<option value=\"$dir/$file\">$file</option>\n";
         }
}
?>
