<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Process;

/**
 * CleanCss filter.
 *
 * @link   https://github.com/jakubpawlowicz/clean-css
 * @author Jakub Pawlowicz <http://JakubPawlowicz.com>
 */
class CleanCssFilter extends BaseNodeFilter
{
    private $cleanCssBin;
    private $nodeBin;

    private $keepLineBreaks;
    private $compatibility;
    private $debug;
    private $rootPath;
    private $skipImport = true;
    private $timeout;
    private $semanticMerging;
    private $roundingPrecision;
    private $removeSpecialComments;
    private $onlyKeepFirstSpecialComment;
    private $skipAdvanced;
    private $skipAggresiveMerging;
    private $skipImportFrom;
    private $mediaMerging;
    private $skipRebase;
    private $skipRestructuring;
    private $skipShorthandCompacting;
    private $sourceMap;
    private $sourceMapInlineSources;


    /**
     * @param string $cleanCssBin Absolute path to the cleancss executable
     * @param string $nodeBin     Absolute path to the folder containg node.js executable
     */
    public function __construct($cleanCssBin = '/usr/bin/cleancss', $nodeBin = null)
    {
        $this->cleanCssBin = $cleanCssBin;
        $this->nodeBin = $nodeBin;
    }

    /**
     * Keep line breaks
     *
     * @param bool $keepLineBreaks True to enable
     */
    public function setKeepLineBreaks($keepLineBreaks)
    {
        $this->keepLineBreaks = $keepLineBreaks;
    }

    /**
     * Remove all special comments
     *
     * @param bool $removeSpecialComments True to enable
     */ // i.e.  /*! comment */
    public function setRemoveSpecialComments($removeSpecialComments)
    {
        $this->removeSpecialComments = $removeSpecialComments;
    }

    /**
     * Remove all special comments except the first one
     *
     * @param bool $onlyKeepFirstSpecialComment True to enable
     */
    public function setOnlyKeepFirstSpecialComment($onlyKeepFirstSpecialComment)
    {
        $this->onlyKeepFirstSpecialComment = $onlyKeepFirstSpecialComment;
    }

    /**
     * Enables unsafe mode by assuming BEM-like semantic stylesheets (warning, this may break your styling!)
     *
     * @param bool $semanticMerging True to enable
     */
    public function setSemanticMerging($semanticMerging)
    {
        $this->semanticMerging = $semanticMerging;
    }

    /**
     * A root path to which resolve absolute @import rules
     *
     * @param string $rootPath
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * Disable @import processing
     *
     * @param bool $skipImport True to enable
     */
    public function setSkipImport($skipImport)
    {
        $this->skipImport = $skipImport;
    }

    /**
     * Per connection timeout when fetching remote @imports; defaults to 5 seconds
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Disable URLs rebasing
     *
     * @param bool $skipRebase True to enable
     */
    public function setSkipRebase($skipRebase)
    {
        $this->skipRebase = $skipRebase;
    }

    /**
     * Disable restructuring optimizations
     *
     * @param bool $skipRestructuring True to enable
     */
    public function setSkipRestructuring($skipRestructuring)
    {
        $this->skipRestructuring = $skipRestructuring;
    }

    /**
     * Disable shorthand compacting
     *
     * @param bool $skipShorthandCompacting True to enable
     */
    public function setSkipShorthandCompacting($skipShorthandCompacting)
    {
        $this->skipShorthandCompacting = $skipShorthandCompacting;
    }

    /**
     * Enables building input's source map
     *
     * @param bool $sourceMap True to enable
     */
    public function setSourceMap($sourceMap)
    {
        $this->sourceMap = $sourceMap;
    }

    /**
     * Enables inlining sources inside source maps
     *
     * @param bool $sourceMapInlineSources True to enable
     */
    public function setSourceMapInlineSources($sourceMapInlineSources)
    {
        $this->sourceMapInlineSources = $sourceMapInlineSources;
    }

    /**
     * Disable advanced optimizations - selector & property merging, reduction, etc.
     *
     * @param bool $skipAdvanced True to enable
     */
    public function setSkipAdvanced($skipAdvanced)
    {
        $this->skipAdvanced = $skipAdvanced;
    }

    /**
     * Disable properties merging based on their order
     *
     * @param bool $skipAggresiveMerging True to enable
     */
    public function setSkipAggresiveMerging($skipAggresiveMerging)
    {
        $this->skipAggresiveMerging = $skipAggresiveMerging;
    }

    /**
     * Disable @import processing for specified rules
     *
     * @param string $skipImportFrom
     */
    public function setSkipImportFrom($skipImportFrom)
    {
        $this->skipImportFrom = $skipImportFrom;
    }

    /**
     * Disable @media merging
     *
     * @param bool $mediaMerging True to enable
     */
    public function setMediaMerging($mediaMerging)
    {
        $this->mediaMerging = $mediaMerging;
    }

    /**
     * Rounds to `N` decimal places. Defaults to 2. -1 disables rounding.
     *
     * @param int $roundingPrecision
     */
    public function setRoundingPrecision($roundingPrecision)
    {
        $this->roundingPrecision = $roundingPrecision;
    }

    /**
     * Force compatibility mode (see https://github.com/jakubpawlowicz/clean-css/blob/master/README.md#how-to-set-compatibility-mode for advanced examples)
     *
     * @param string $compatibility
     */
    public function setCompatibility($compatibility)
    {
        $this->compatibility = $compatibility;
    }

    /**
     * Shows debug information (minification time & compression efficiency)
     *
     * @param bool $debug True to enable
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }


    /**
     * @see FilterInterface::filterLoad()
     */
    public function filterLoad(AssetInterface $asset)
    {
    }


    /**
     * Run the asset through CleanCss
     *
     * @see FilterInterface::filterDump()
     */
    public function filterDump(AssetInterface $asset)
    {
        $commandline = $this->getBinary();
        $formats = array();
        $optimizations = array();

        if ($this->keepLineBreaks) {
            if ($this->isV4()) {
                $format[] = 'keep-breaks';
            } else {
                array_push($commandline, '--keep-line-breaks');
            }
        }

        if ($this->compatibility) {
            array_push($commandline, '--compatibility ' . $this->compatibility);
        }

        if ($this->debug) {
            array_push($commandline, '--debug');
        }

        if ($this->rootPath) {
            if ($this->isV4()) {
                $this->deprecatedOption('rootPath');
            } else {
                array_push($commandline, '--root ' . $this->rootPath);
            }
        }

        if ($this->skipImport) {
            if ($this->isV4()) {
                array_push($commandline, '--inline=none');
            } else {
                array_push($commandline, '--skip-import');
            }
        }

        if ($this->timeout) {
            if ($this->isV4()) {
                array_push($commandline, '--inline-timeout' . $this->timeout);
            } else {
                array_push($commandline, '--timeout ' . $this->timeout);
            }
        }

        if ($this->roundingPrecision) {
            if ($this->isV4()) {
                $this->deprecatedOption('roundingPrecision');
            } else {
                array_push($commandline, '--rounding-precision ' . $this->roundingPrecision);
            }
        }

        if ($this->removeSpecialComments) {
            if ($this->isV4()) {
                $optimizations[] = 'specialComments:0';
            } else {
                array_push($commandline, '--s0');
            }
        }

        if ($this->onlyKeepFirstSpecialComment) {
            if ($this->isV4()) {
                $optimizations[] = 'specialComments:1';
            } else {
                array_push($commandline, '--s1');
            }
        }

        if ($this->semanticMerging) {
            if ($this->isV4()) {
                $this->deprecatedOption('semanticMerging');
            } else {
                array_push($commandline, '--semantic-merging');
            }
        }

        if ($this->skipAdvanced) {
            $this->deprecatedOption('skipAdvanced');
            array_push($commandline, '--skip-advanced');
        }

        if ($this->skipAggresiveMerging) {
            if ($this->isV4()) {
                $this->deprecatedOption('skipAggresiveMerging');
            } else {
                array_push($commandline, '--skip-aggressive-merging');
            }
        }

        if ($this->skipImportFrom) {
            $this->deprecatedOption('skipImportFrom');
            array_push($commandline, '--skip-import-from ' . $this->skipImportFrom);
        }

        if ($this->mediaMerging) {
            if ($this->isV4()) {
                $this->deprecatedOption('mediaMerging');
            } else {
                array_push($commandline, '--skip-media-merging');
            }
        }

        if ($this->skipRebase) {
            $this->deprecatedOption('skipRebase');
            array_push($commandline, '--skip-rebase');
        }

        if ($this->skipRestructuring) {
            if ($this->isV4()) {
                $this->deprecatedOption('skipRestructuring');
            } else {
                array_push($commandline, '--skip-restructuring');
            }
        }

        if ($this->skipShorthandCompacting) {
            $this->deprecatedOption('skipShorthandCompacting');
            array_push($commandline, '--skip-shorthand-compacting');
        }

        if ($this->sourceMap) {
            array_push($commandline, '--source-map');
        }

        if ($this->sourceMapInlineSources) {
            array_push($commandline, '--source-map-inline-sources');
        }

        if (!empty($formats)) {
            array_push($commandline, '--format', implode(';', $formats));
        }

        if (!empty($optimizations)) {
            array_push($commandline, '-O1', implode(';', $optimizations));
        }

        // input and output files
        $input = tempnam(sys_get_temp_dir(), 'input');

        file_put_contents($input, $asset->getContent());
        array_push($commandline, $input);

        $proc = Process::fromShellCommandline(implode(' ', $commandline));
        $code = $proc->run();
        unlink($input);

        if (127 === $code) {
            throw new \RuntimeException('Path to node executable could not be resolved.');
        }

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    private function deprecatedOption($name)
    {
        if ($this->isV4()) {
            @trigger_error("cleancss '$name' option is not supported in 4.0", E_USER_DEPRECATED);
        }
    }

    private function isV4()
    {
        return version_compare($this->getVersion(), '4', '>=');
    }

    private function getVersion()
    {
        $commandline = $this->getBinary();
        array_push($commandline, '--version');

        $proc = Process::fromShellCommandline(implode(' ', $commandline));
        $code = $proc->run();

        return $proc->getOutput();
    }

    private function getBinary()
    {
        return $this->nodeBin
            ? array($this->nodeBin, $this->cleanCssBin)
            : array($this->cleanCssBin);
    }
}
