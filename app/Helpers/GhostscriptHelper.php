<?php
namespace App\Helpers;

use Exception;
use Symfony\Component\Process\Process;

class GhostscriptHelper
{
    private $bin_path;
    private $input_files;
    private $output_file;
    
    public function __construct($bin_path) {
        $this->bin_path = $bin_path;
        $this->input_files = [];
    }
    
    public function addInputFile($file_path) {
        $this->input_files[] = $file_path;
    }
    
    public function setOutputFile($file_path) {
        $this->output_file = $file_path;
    }

  
    public function merge() {
       
        if (empty($this->input_files)) {
        throw new Exception("No input files provided.");
        }
        if (empty($this->output_file)) {
        throw new Exception("No output file provided.");
        }
        $input_files_str = implode(" ", $this->input_files);
        $cmd = "{$this->bin_path} -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile={$this->output_file} {$input_files_str}";
        $output = shell_exec($cmd);
        if ($output !== null) {
            throw new Exception("Failed to execute Ghostscript command. Output: {$output}");
        }
    }
}
