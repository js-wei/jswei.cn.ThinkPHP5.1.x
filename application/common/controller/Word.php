<?php
# @Author: 魏巍
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  jswei30@gmail.com
# @Filename: Word.php
# @Last modified by:   魏巍
# @Last modified time: 2017-11-20T12:38:38+08:00


namespace  app\common\controller;

use PhpOffice\PhpWord\Settings;

class Word
{
    protected function initialize()
    {
        date_default_timezone_set('UTC');
        error_reporting(E_ALL);
        define('CLI', (PHP_SAPI == 'cli') ? true : false);
        define('EOL', CLI ? PHP_EOL : '<br />');
        define('SCRIPT_FILENAME', basename($_SERVER['SCRIPT_FILENAME'], '.php'));
        define('IS_INDEX', SCRIPT_FILENAME == 'index');
        Settings::loadConfig();
        // Set writers
        $writers = array('Word2007' => 'docx', 'ODText' => 'odt', 'RTF' => 'rtf', 'HTML' => 'html', 'PDF' => 'pdf');
        // Set PDF renderer
        if (null === Settings::getPdfRendererPath()) {
            $writers['PDF'] = null;
        }
        // Turn output escaping on
        Settings::setOutputEscapingEnabled(true);
        // Return to the caller script when runs by CLI
        if (CLI) {
            return;
        }

        // Set titles and names
        $pageHeading = str_replace('_', ' ', SCRIPT_FILENAME);
        $pageTitle = IS_INDEX ? 'Welcome to ' : "{$pageHeading} - ";
        $this->pageTitle = $pageTitle .= 'PHPWord';
        $this->pageHeading = IS_INDEX ? '' : "<h1>{$pageHeading}</h1>";

        // Populate samples
        $this->files = '';
        if ($handle = opendir('.')) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/^Sample_\d+_/', $file)) {
                    $name = str_replace('_', ' ', preg_replace('/(Sample_|\.php)/', '', $file));
                    $this->files .= "<li><a href='{$file}'>{$name}</a></li>";
                }
            }
            closedir($handle);
        }
    }
    /**
     * Write documents
     *
     * @param \PhpOffice\PhpWord\PhpWord $phpWord
     * @param string $filename
     * @param array $writers
     *
     * @return string
     */
    public function write($phpWord, $filename, $writers)
    {
        $result = '';

        // Write documents
        foreach ($writers as $format => $extension) {
            $result .= date('H:i:s') . " Write to {$format} format";
            if (null !== $extension) {
                $targetFile = __DIR__ . "/results/{$filename}.{$extension}";
                $phpWord->save($targetFile, $format);
            } else {
                $result .= ' ... NOT DONE!';
            }
            $result .= EOL;
        }

        $result .= getEndingNotes($writers);

        return $result;
    }

    public function html()
    {
        $html = <<<EOT
<title>{$this->pageTitle}</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="bootstrap/css/font-awesome.min.css" />
<link rel="stylesheet" href="bootstrap/css/phpword.css" />
</head>
<body>
<div class="container">
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="./">PHPWord</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="dropdown active">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-code fa-lg"></i>&nbsp;Samples<strong class="caret"></strong></a>
                    <ul class="dropdown-menu">{$this->files}</ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="https://github.com/PHPOffice/PHPWord"><i class="fa fa-github fa-lg" title="GitHub"></i>&nbsp;</a></li>
                <li><a href="http://phpword.readthedocs.org/"><i class="fa fa-book fa-lg" title="Docs"></i>&nbsp;</a></li>
                <li><a href="http://twitter.com/PHPWord"><i class="fa fa-twitter fa-lg" title="Twitter"></i>&nbsp;</a></li>
            </ul>
        </div>
    </div>
</div>
{$this->pageHeading}
</div>
<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
EOT;

        return $html;
    }

    /**
     * Get ending notes
     *
     * @param array $writers
     *
     * @return string
     */
    public function getEndingNotes($writers)
    {
        $result = '';

        // Do not show execution time for index
        if (!IS_INDEX) {
            $result .= date('H:i:s') . " Done writing file(s)" . EOL;
            $result .= date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB" . EOL;
        }

        // Return
        if (CLI) {
            $result .= 'The results are stored in the "results" subdirectory.' . EOL;
        } else {
            if (!IS_INDEX) {
                $types = array_values($writers);
                $result .= '<p>&nbsp;</p>';
                $result .= '<p>Results: ';
                foreach ($types as $type) {
                    if (!is_null($type)) {
                        $resultFile = 'results/' . SCRIPT_FILENAME . '.' . $type;
                        if (file_exists($resultFile)) {
                            $result .= "<a href='{$resultFile}' class='btn btn-primary'>{$type}</a> ";
                        }
                    }
                }
                $result .= '</p>';
            }
        }

        return $result;
    }
}
