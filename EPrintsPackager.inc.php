<?php

require_once('./lib/pkp/lib/swordapp/packager_mets_swap.php');

class EPrintsPackager extends PackagerMetsSwap {

  private $tagArray;
  private $nextLine;

  //additional metadata fields
	public $sac_subject;
	public $sac_datecreated;
	public $sac_source;
	public $sac_sponsor;
	public $sac_publisher;
	public $sac_access;
	public $sac_discipline;
	public $sac_language;
	public $sac_URN;

	function __construct($sac_rootin, $sac_dirin, $sac_rootout, $sac_fileout) {
		parent::__construct($sac_rootin, $sac_dirin, $sac_rootout, $sac_fileout);
		$this->sac_metadata_filename = "eprintsMetadata.xml";
	}

  function setSubject($s) {
    $this->sac_subject = $this->clean($s);
  }
  
	function setDateCreated($s) {
    $this->sac_datecreated = $this->clean($s);
  }
	
	function setSource($s) {
    $this->sac_source = $this->clean($s);
  }
	function setPublication($s) {
    $this->sac_publication = $this->clean($s);
  }

	function setSponsor($s) {
    $this->sac_sponsor = $this->clean($s);
  }
	
	function setPublisher($s) {
    $this->sac_publisher = $this->clean($s);
  }

	function setAccess($s) {
    $this->sac_access = $s;
  }
	function setURN($s) {
    $this->sac_URN = $s;
  }
	function setNumber($s) {
    $this->sac_number = $s;
  }

	function setVolume($s) {
    $this->sac_volume = $s;
  }
	function setDate($s) {
    $this->sac_date = $s;
  }

	function setDiscipline($s) {
    $this->sac_discipline = $this->clean($s);
  }

  function setLanguage($s) {
    $this->sac_language = $s;
  }
  
    function addEPCreator($sac_creator_data) {
	foreach($sac_creator_data as $k=>$v){
		$cleaned_creator_data[$k] = $this->clean($v);
	}
        array_push($this->sac_creators, $cleaned_creator_data);

    }

	function create() {
		// Write the metadata file
		$fh = @fopen($this->sac_root_in . '/' . $this->sac_dir_in . '/' . $this->sac_metadata_filename, 'w');
		if (!$fh) {
				throw new Exception("Error writing metadata file (" . 
														$this->sac_root_in . '/' . $this->sac_dir_in . $this->sac_metadata_filename . ")");
		}
		$this->createXML($fh);
		fclose($fh);

		// Create the zipped package
		$zip = new ZipArchive();
		$zip->open($this->sac_root_out . '/' . $this->sac_file_out, ZIPARCHIVE::CREATE);
		$zip->addFile($this->sac_root_in . '/' . $this->sac_dir_in . '/'. $this->sac_metadata_filename, $this->sac_metadata_filename);
#		$zip->addEmptyDir('data');
		for ($i = 0; $i < $this->sac_filecount; $i++) {
#			$zip->addFile($this->sac_root_in . '/' . $this->sac_dir_in . '/' . $this->sac_files[$i], 
#											'data/'.$this->sac_files[$i]);
			$zip->addFile($this->sac_root_in . '/' . $this->sac_dir_in . '/' . $this->sac_files[$i], 
											$this->sac_files[$i]);

		}
		$zip->close();
	}

	function createXML($fh) {
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->appendChild($eprints = $doc->createElement("eprints"));
		$eprints->appendChild($eprint = $doc->createElement("eprint"));
		$eprint->appendChild($doc->createElement("title", $this->sac_title));
		$eprint->appendChild($doc->createElement("abstract", $this->sac_abstract));
		$eprint->appendChild($doc->createElement("publication", $this->sac_publication));
		$eprint->appendChild($creators = $doc->createElement("creators"));
		if(isset($this->sac_URN)){
#			error_log("Adding EPRINTID :".$this->sac_URN);
			$eprint->appendChild($doc->createElement("eprintid", $this->sac_URN));
		}

		$eprint->appendChild($doc->createElement("volume", $this->sac_volume));
		$eprint->appendChild($doc->createElement("number", $this->sac_number));
		$eprint->appendChild($doc->createElement("date", $this->sac_date));


		foreach($this->sac_creators as $creator_data){
			$creators->appendChild($item = $doc->createElement("item"));	
			$item->appendChild($name = $doc->createElement("name"));
			foreach($creator_data as $k=>$v){
				$name->appendChild($doc->createElement($k, $v));
			}
		}

		$eprint->appendChild($documents = $doc->createElement("documents"));
		for ($i = 0; $i < $this->sac_filecount; $i++) {
			$documents->appendChild($document = $doc->createElement("document"));
			$file_cmd = '/usr/bin/file -bi '.$this->sac_root_in . '/' . $this->sac_dir_in . '/' . $this->sac_files[$i];
			$format = `$file_cmd`;
			$format = rtrim($format);

			$document->appendChild($doc->createElement("format" , $format));
#			$document->appendChild($doc->createElement("formatdesc", "PDF"));
			$document->appendChild($doc->createElement("security", "public"));
			$document->appendChild($doc->createElement("main", $this->sac_files[$i]));

			$document->appendChild($files = $doc->createElement("files"));

			$files->appendChild($file = $doc->createElement("file"));
			$file->appendChild($doc->createElement("url", $this->sac_files[$i]));
			$file->appendChild($doc->createElement("filename", $this->sac_files[$i]));
			$file->appendChild($doc->createElement("data", $this->sac_files[$i]));


		}

		fwrite($fh,$doc->saveXML());	
	}

}

?>
