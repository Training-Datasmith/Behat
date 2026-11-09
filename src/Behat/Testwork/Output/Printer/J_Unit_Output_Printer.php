<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Behat\Util\Strict_Regex;
use Behat\Testwork\Output\Exception\Missing_Extension_Exception;
use Behat\Testwork\Output\Exception\Missing_Output_Path_Exception;
use Behat\Testwork\Output\Printer\Factory\Filesystem_Output_Factory;
use DOMDocument;
use DOMElement;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A convenient wrapper around the ConsoleOutputPrinter to write valid JUnit
 * reports.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author James Watson <james@sitepulse.org>
 */
final class J_Unit_Output_Printer extends Stream_Output_Printer
{
    public const XML_VERSION = '1.0';
    public const XML_ENCODING = 'UTF-8';
    private ?DOMDocument $dom_document = null;
    private DOMElement $current_testsuite;
    private DOMElement $current_testcase;
    private DOMElement $test_suites;

    public function __construct(Filesystem_Output_Factory $output_factory)
    {
        parent::__construct($output_factory);
    }

    /**
     * Creates a new JUnit file.
     *
     * The file will be initialized with an XML definition and the root element.
     *
     * @param string $name                 The filename (without extension) and default value of the name attribute
     */
    public function create_new_file(string $name, array $testsuites_attributes = []): void
    {
        // This requires the DOM extension to be enabled.
        if (!extension_loaded('dom')) {
            throw new Missing_Extension_Exception('The PHP DOM extension is required to generate JUnit reports.');
        }
        $this->set_file_name(strtolower(trim(Strict_Regex::replace('/[^[:alnum:]_]+/', '_', $name), '_')));
        $this->dom_document = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->dom_document->format_output = true;
        $this->test_suites = $this->dom_document->createElement('testsuites');
        $this->dom_document->appendChild($this->test_suites);
        $this->add_attributes_to_node($this->test_suites, array_merge(['name' => $name], $testsuites_attributes));
        $this->flush();
    }

    /**
     * Adds a new <testsuite> node.
     */
    public function add_testsuite(array $testsuite_attributes = []): void
    {
        $this->current_testsuite = $this->dom_document->createElement('testsuite');
        $this->test_suites->appendChild($this->current_testsuite);
        $this->add_attributes_to_node($this->current_testsuite, $testsuite_attributes);
    }

    /**
     * Extends the current <testsuite> node.
     */
    public function extend_testsuite_attributes(array $testsuite_attributes): void
    {
        $this->add_attributes_to_node($this->current_testsuite, $testsuite_attributes);
    }

    /**
     * Adds a new <testcase> node.
     */
    public function add_testcase(array $testcase_attributes = []): void
    {
        $this->current_testcase = $this->dom_document->createElement('testcase');
        $this->current_testsuite->appendChild($this->current_testcase);
        $this->add_attributes_to_node($this->current_testcase, $testcase_attributes);
    }

    /**
     * Adds attributes to the current <testcase> node.
     */
    public function add_current_test_case_attributes(array $testcase_attributes): void
    {
        $this->add_attributes_to_node($this->current_testcase, $testcase_attributes);
    }

    /**
     * Add a testcase child element.
     */
    public function add_testcase_child($node_name, array $node_attributes = [], $node_value = null): void
    {
        $child_node = $this->dom_document->createElement($node_name, $node_value ?? '');
        $this->current_testcase->appendChild($child_node);
        $this->add_attributes_to_node($child_node, $node_attributes);
    }

    private function add_attributes_to_node(DOMElement $node, array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $node->setAttribute($name, $value ?? '');
        }
    }

    /**
     * Sets file name.
     *
     * @param string $extension The file extension, defaults to "xml"
     */
    public function set_file_name(string $file_name, string $extension = 'xml'): void
    {
        if ('.' . $extension !== substr($file_name, strlen($extension) + 1)) {
            $file_name .= '.' . $extension;
        }
        $output_factory = $this->get_output_factory();
        assert($output_factory instanceof Filesystem_Output_Factory);
        $output_factory->set_file_name($file_name);
        $this->flush();
    }

    /**
     * Generate XML from the DOMDocument and send to the writing stream.
     */
    public function flush(): void
    {
        if ($this->dom_document instanceof DOMDocument) {
            try {
                $this->get_writing_stream()->write($this->dom_document->saveXml(null, LIBXML_NOEMPTYTAG), false, OutputInterface::OUTPUT_RAW);
            } catch (Missing_Output_Path_Exception) {
                throw new Missing_Output_Path_Exception('The `output_path` option must be specified for the junit formatter.');
            }
        }
        parent::flush();
    }
}
