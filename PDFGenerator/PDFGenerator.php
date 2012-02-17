<?php

namespace Spraed\PDFGeneratorBundle\PDFGenerator;

class PDFGenerator {

    /**
     * @param type $html - html to generate the pdf from
     * @param type $pdfFile - name of the pdf file
     * @param type $downloadable - offer pdf as download or open in new browser tab
     */
    public function generatePDF($html, $encoding) {

        $pdfFile = $this->createTemporaryFile('out', 'pdf');
        $htmlFile = $this->createTemporaryFile('pdf_html', 'html', $html);

        $result = $this->generate($htmlFile, $encoding, $pdfFile);

        // remove temporary file from hdd
        unlink($htmlFile);
        return $result;
    }

    /**
     * @param type $htmlFile - the temporary html file the pdf is generated from
     * @param type $pdfFile - the temporaray pdf file which the stream will be written to
     * @return string
     */
    private function buildCommand($htmlFile, $encoding, $pdfFile) {
        $command = 'java -jar ';
        $command .= '"' . __DIR__ . '/../Resources/java/spraed-pdf-generator.jar"';
        $command .= ' --html "' . $htmlFile . '" --pdf "' . $pdfFile . '"';
        $command .= ' --encoding ' . $encoding;

        return $command;
    }

    /**
     * @param type $htmlFile - the temporary html file the pdf is generated from
     * @param type $pdfFile - the temporaray pdf file which the stream will be written to
     * @param type $pdfFileName -  filename of the pdf
     * @param type $downloadable - offer pdf as download or open in new browser tab
     */
    public function generate($htmlFile, $encoding, $pdfFile) {

        $command = $this->buildCommand($htmlFile, $encoding, $pdfFile);

        list($status, $stdout, $stderr) = $this->executeCommand($command);

        //TODO Check state, out, and err

        $pdf = file_get_contents($pdfFile);
        unlink($pdfFile);
        return $pdf;
    }

    /**
     *
     * @param type $command - the command which will be executed to generate the pdf
     * @return type
     */
    public function executeCommand($command) {
        $stdout = $stderr = $status = null;
        $descriptorspec = array(
            1 => array('pipe', 'w'), // stdout is a pipe that the child will write to
            2 => array('pipe', 'w') // stderr is a pipe that the child will write to
        );

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
     * @param type $extension - extension of file (pdf)
     * @param type $content - content to be put in generated file
     * @return string
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
