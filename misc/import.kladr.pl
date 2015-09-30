#!/usr/bin/perl -w
use DBI;

@list = qx{ls -1 *.DBF};

foreach $file (@list)
{
	chomp($file);
	printf("Processing ".$file."...\n");
	@parts = split(/\./, $file);
	$csvname = $parts[0].".CSV";
	$outname = $parts[0]."-utf8.CSV";
	printf("Writing ".$csvname."\n");
	qx{perl dbf2csv.pl -f ";" -e "" -o $csvname $file};
	printf("Converting to UTF-8...\n");
	qx{iconv -f cp866 -t utf8 -o $outname $csvname};
	unlink $csvname or warn "Could not delete ".$csvname;
}; # foreach

# connect to database
print "connecting database... \n";
my $database = DBI->connect("DBI:Pg:dbname=usi2030;host=192.168.56.5;port=5432;","usi","usi") or die $DBI::errstr;

my $query = ();

# write SOCRBASE table
open(STDIN, "SOCRBASE-utf8.CSV") or die "cannot open file";
while ($line = <STDIN>)
{
  chomp($line);
  @fields = split(/;/, $line);
  printf("Adding SOCRBASE $fields[0] $fields[1]  $fields[2]...\n");
  $query = $database->prepare(qq{SELECT kladr.add_socrbase($fields[1], $fields[2])})
    or die "Error preparing query \n";
  $query->execute() or die "Error executing query \n";
  $query->finish();
}

# write KLADR table
open(STDIN, "KLADR-utf8.CSV") or die "cannot open file";
while ($line = <STDIN>)
{
  chomp($line);
  @fields = split(/;/, $line);
  printf("Adding KLADR $fields[0] $fields[1]...\n");
  $query = $database->prepare(qq{SELECT kladr.add_kladr($fields[0], $fields[1], $fields[2], $fields[3], $fields[7])}) or die "Error preparing query \n";
  $query->execute() or die "Error executing query \n";
  $query->finish();
}

open(STDIN, "STREET-utf8.CSV") or die "cannot open file";
while($line = <STDIN>)
{
  chomp($line);
  @fields = split(/;/, $line);
  printf("Adding street $fields[0] $fields[1] $fields[2]...\n");
  $query = $database->prepare(qq{SELECT kladr.add_street($fields[0], $fields[1], $fields[2], $fields[3])}) or die "Error preparing query \n";
  $query->execute() or die "Error executing query \n";
  $query->finish();
}

# disconnecting...
$database->disconnect();
