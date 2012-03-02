<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

class PDFGenerator {

	/**
	 * @param type $html - html to generate the pdf from
	 * @param type $encoding - set the html (input) and pdf (output) encoding, defaults to UTF-8
	 */
	public function generatePDF($html, $encoding = 'UTF-8') {

		return $this->generatePDFs(array($html), $encoding);
	}

    /**
     * @param type $html - html to generate the pdf from
     * @param type $encoding - set the html (input) and pdf (output) encoding, defaults to UTF-8
     */
    public function generatePDFs($htmls, $encoding = 'UTF-8') {

        $pdfFile = $this->createTemporaryFile('output', 'pdf');

        $htmlFiles = array();
        foreach($htmls as $html){
            $htmlFiles[] = $this->createTemporaryFile('pdf_html', 'html', $html);
        }

        $result = $this->generate($htmlFiles, $encoding, $pdfFile);

        // remove temporary file
        foreach($htmlFiles as $htmlFile){
            unlink($htmlFile);
        }

        return $result;
    }

	/**
	 * @param type $htmlFile - the temporary html file the pdf is generated from
	 * @param type $encoding - set the html (input) and pdf (output) encoding
	 * @param type $pdfFile - the temporaray pdf file which the stream will be written to
	 * @return pdf
	 */
	public function generate($htmlFiles, $encoding, $pdfFile) {

		$command = $this->buildCommand($htmlFiles, $encoding, $pdfFile);

		list($status, $stdout, $stderr) = $this->executeCommand($command);

		//TODO Check state, out, and err

		$pdf = file_get_contents($pdfFile);
		unlink($pdfFile);
		return $pdf;
	}

	/**
	 * @param type $htmlFile - the temporary html file the pdf is generated from
	 * @param type $encoding - set the html (input) and pdf (output) encoding
	 * @param type $pdfFile - the temporaray pdf file which the stream will be written to
	 * @return command
	 */
	private function buildCommand($htmlFiles, $encoding, $pdfFile) {

        $htmlFile = implode(', ', $htmlFiles);

		$command = 'java -jar ';
		$command .= '"' . __DIR__
				. '/../Resources/java/spraed-pdf-generator.jar"';
		$command .= ' --html "' . $htmlFile . '" --pdf "' . $pdfFile . '"';
		$command .= ' --encoding ' . $encoding;

		return $command;
	}

	/**
	 * @param type $command - the command which will be executed to generate the pdf
	 */
	public function executeCommand($command) {
		$stdout = $stderr = $status = null;
		$descriptorspec = array(
				// stdout is a pipe that the child will write to
				1 => array('pipe', 'w'), 
				// stderr is a pipe that the child will write to
				2 => array('pipe', 'w'));

		$process = proc_open($command, $descriptorspec, $pipes);

		if (is_resource($process)) {
			// $pipes now looks like this:
			// 0 => writeable handle connected to child stdin
			// 1 => readable handle connected to child stdout
			// 2 => readable handle connected to child stderr

			$stdout = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			$stderr = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			// It is important that you close any pipes before calling
			// proc_close in order to avoid a deadlock
			$status = proc_close($process);
		}

		return array($status, $stdout, $stderr);
	}

	/**
	 * @param type $filename - filename of the pdf
	 * @param type $extension - extension of file
	 * @param type $content - content to be put in generated file
	 * @return file
	 */
	private function createTemporaryFile($filename, $extension, $content = null) {
		$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid($filename)
				. '.' . $extension;

		if (null !== $content) {
			file_put_contents($file, $content);
		}

		return $file;
	}

}
