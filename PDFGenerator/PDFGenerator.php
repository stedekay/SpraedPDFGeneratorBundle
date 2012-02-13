<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;
class PDFGenerator {

	public function generatePDF($html, $pdfFile = null) {

		if ($pdfFile === null) {
			$pdfFile = 'output';
		}

		$pdfFile = $this->createTemporaryFile($pdfFile, 'pdf');
		$htmlFile = $this->createTemporaryFile('pdf_html', 'html', $html);

		$command = $this->buildCommand($htmlFile, $pdfFile);

		$result = $this->generate($htmlFile, $pdfFile);

		unlink($htmlFile);
	}

	private function buildCommand($htmlFile, $pdfFile) {
		$command = 'java -jar ';
		$command .= __DIR__ . '/../Resources/java/spraed-pdf-generator.jar ';
		$command .= $htmlFile . ' ' . $pdfFile;

		return $command;
	}

	private function createTemporaryFile($filename, $extension, $content = null) {
		$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid($filename)
				. '.' . $extension;

		if (null !== $content) {
			file_put_contents($file, $content);
		}

		return $file;
	}
}
