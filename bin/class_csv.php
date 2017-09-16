<?php
/**
 * CSV Utils
 * 
 * This is a csv reader - basically it reads a csv file into an array
 * Please read the LICENSE file
 * @copyright Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke@mc2design.com>
 * @package Csv
 * @license GNU Lesser General Public License
 * @version 0.1
 */

/**
 * Provides an easy-to-use interface for reading csv-formatted text files. It
 * makes use of the function fgetcsv. It provides quite a bit of flexibility.
 * You can specify just about everything about how it should read a csv file
 * @todo Research the ArrayIterator class and see if it is the best choice for
 *       this and if I'm even using it correctly. There are quite a few methods 
 *       that are inherited that may or may not work. It would be cool if we
 *       could use 
 * @package Csv
 * @subpackage Csv_Reader
 */

if (phpversion() < "5")	{
	echo "Warning ! CSV Class can be use due to PHP version less than 5 !<br>";
	return;
}

class CSV_Reader implements Iterator, Countable
{
    /**
     * Maximum row size
     * @todo Should this be editable? maybe change it to a public variable
     */
    const MAX_ROW_SIZE = 4096;
    /**
     * Path to csv file
     * @var string
     * @access protected
     */
    protected $path;
    /**
     * Tells reader how to read the file
     * @var Csv_Dialect
     * @access protected
     */
    protected $dialect;
    /**
     * A handle that points to the file we are reading
     * @var resource
     * @access protected
     */
    protected $handle;
    /**
     * The currently loaded row
     * @var array
     * @access public
     * @todo: Should this be public? I think it might have been required for ArrayIterator to work properly
     */
    public $current;
    /**
     * This is the current line position in the file we're reading 
     * @var integer
     */
    protected $position = 0;
    /**
     * Number of lines skipped due to malformed data
     * @var integer
     * @todo This may be flawed - be sure to test it thoroughly
     */
    protected $skippedlines = 0;
    /**
     * Class constructor
     *
     * @param string Path to csv file we want to open
     * @param string The character(s) used to seperate columns in the csv file
     * @param boolean If set to false, don't treat the first row as headers - defaults to true
     * @throws Csv_Exception
     */
    public function __construct($path, Csv_Dialect $dialect = null/*, $skip_empty_rows = false*/) {
    
        if (is_null($dialect)) $dialect = new Csv_Dialect;
        $this->dialect = $dialect;
        // open the file
        $this->setPath($path);
        $this->handle = fopen($this->path, 'rb');
        if ($this->handle === false) 
        	echo '<p>File does not exist or is not readable.</p>';
        $this->rewind();
    
    }
    /**
     * Get the current Csv_Dialect object
     *
     * @return The current Csv_Dialect object
     * @access public
     */
    public function getDialect() {
    
        return $this->dialect;
    
    }
    /**
     * Change the dialect this csv reader is using
     *
     * @param Csv_Dialect the current Csv_Dialect object
     * @access public
     */
    public function setDialect(Csv_Dialect $dialect) {
    
        $this->dialect = $dialect;
    
    }
    /**
     * Set the path to the csv file
     *
     * @param string The full path to the file we want to read
     * @access protected
     */
    protected function setPath($path) {
    
        $this->path = realpath($path);
    
    }
    /**
     * Get the path to the csv file we're reading
     *
     * @return string The path to the file we are reading
     * @access public
     */
    public function getPath() {
    
        return $this->path;
    
    }
    /**
     * Removes the escape character in front of our quote character
     *
     * @param string The input we are unescaping
     * @param string The key of the item
     * @todo Is the second param necssary? I think it is because array_walk
     */
    protected function unescape(&$item, $key) {
    
        $item = str_replace($this->dialect->escapechar.$this->dialect->quotechar, $this->dialect->quotechar, $item);
    
    }
    /**
     * Returns the current row and calls next()
     * 
     * @access public
     */
    public function getRow() {
    
        $return = $this->current();
        $this->next();
        return $return;
    
    }
    /**
     * Loads the current row into memory
     * 
     * @access protected
     * @todo I can't get fgetcsv to choke on anything, so throwing an exception here may not be possible
     */
    protected function loadRow() {
    
        if (!$this->current = fgetcsv($this->handle, self::MAX_ROW_SIZE, $this->dialect->delimiter, $this->dialect->quotechar)) {
            //sthrow new Csv_Exception('Invalid format for row ' . $this->position);
        }
        if (
            $this->dialect->escapechar !== ''
            && $this->dialect->escapechar !== $this->dialect->quotechar
            && is_array($this->current)
        ) array_walk($this->current, array($this, 'unescape'));
        // if this row is blank and dialect says to skip blank lines, load in the next one and pretend this never happened
        if ($this->dialect->skipblanklines && is_array($this->current) && count($this->current) == 1 && $this->current[0] == '') {
            $this->skippedlines++;
            $this->next();
        }
    
    }
    /**
     * Get number of lines that were skipped
     * @todo probably should return an array with actual data instead of just the amount
     */
    public function getSkippedLines() {
    
        return $this->skippedlines;
    
    }
    /**
     * Returns csv data as an array
     * @todo if first param is set to true the header row is used as keys
     */
    public function toArray() {
    
        $return = array();
        foreach ($this as $row) {
            $return[] = $row;
        }
        // be kinds, please rewind
        $this->rewind();
        return $return;
    
    }
    /**
     * Get total rows
     *
     * @return integer The number of rows in the file (not includeing line-breaks in the data)
     * @todo Make sure that this is aware of line-breaks in data as opposed to end of row
     * @access public
     */
    public function close() {
    
        if (is_resource($this->handle)) fclose($this->handle);
    
    }
    /**
     * Destructor method - Closes the file handle
     * 
     * @access public
     */
    public function __destruct() {

        $this->close();

    }
    
    /**
     * The following are the methods required by php's Standard PHP Library - Iterator, Countable Interfaces
     */
    
    /**
     * Advances the internal pointer to the next row and returns it if valid, otherwise it returns false
     * 
     * @access public
     * @return boolean|array An array of data if valid, or false if not
     */
    public function next() {
    
        $this->position++;
        $this->loadRow(); // loads the current row into memory
        return ($this->valid()) ? $this->current : false;
    
    }
    /**
     * Tells whether or not the current row is valid - called after next and rewind
     * 
     * @access public
     * @return boolean True if the current row is valid
     */
    public function valid() {
    
        if (is_resource($this->handle))
            return (boolean) !feof($this->handle);
        
        return false;
    
    }
    /**
     * Returns the current row 
     * 
     * @access public
     * @return array An array of the current row's data
     */
    public function current() {
    
        return $this->current;
    
    }
    /**
     * Moves the internal pointer to the beginning
     * 
     * @access public
     */
    public function rewind() {
    
        rewind($this->handle);
        $this->position = 0;
        $this->loadRow(); // loads the current (first) row into memory 
    
    }
    /**
     * Returns the key of the current row (position of pointer)
     * 
     * @access public
     * @return integer
     */
    public function key() {
    
        return (integer) $this->position;
    
    }
    /**
     * Returns the number of rows in the csv file
     * 
     * @access public
     * @return integer
     * @todo Should this remember the position the file was in or something?
     */
    public function count() {
    
        $lines = 0;
        foreach ($this as $row) $lines++;
        $this->rewind();
        return (integer) $lines;
    
    }
}



/**
 * Provides an easy-to-use interface for writing csv-formatted text files. It
 * does not make use of the PHP5 function fputcsv. It provides quite a bit of
 * flexibility. You can specify just about everything about how it writes csv
 * @package Csv
 * @subpackage Csv_Writer
 */
class CSV_Writer
{
    /**
     * The filename of the file we're working on
     * @var string
     * @access protected
     */
    protected $filename;
    /**
     * Holds an instance of Csv_Dialect - tells writer how to write
     * @var Csv_Dialect 
     * @access protected
     */
    protected $dialect;
    /**
     * Holds the file resource
     * @var resource
     * @access protected
     */
    protected $handle;
    /**
     * Contains the in-menory data waiting to be written to disk
     * @var array
     * @access protected
     */
    protected $data = array();
    /**
     * Class constructor
     *
     * @param resource|string Either a valid filename or a valid file resource
     * @param Csv_Dialect A Csv_Dialect object
     * @todo: Allow the user to pass in a file handle (this way they can specify
     *        to append rather than overwrite or visa versa)
     */
    public function __construct($file, $dialect = null) {
    
        if (is_null($dialect)) $dialect = new Csv_Dialect();
        if (is_resource($file))
            $this->handle = $file;
        else
            $this->filename = $file;
        
        $this->dialect = $dialect;
    
    }
    /**
     * Get the current Csv_Dialect object
     *
     * @returns Csv_Dialect object
     * @access public
     */
    public function getDialect() {
    
        return $this->dialect;
    
    }
    /**
     * Change the dialect this csv reader is using
     *
     * @param Csv_Dialect the current Csv_Dialect object
     * @access public
     */
    public function setDialect(Csv_Dialect $dialect) {
    
        $this->dialect = $dialect;
    
    }
    /**
     * Get the filename attached to this writer (unless none was specified)
     *
     * @return string|null The filename this writer is attached to or null if it
     *         was passed a resource and no filename
     * @todo Add a functions file so that you can use convenience functions like
     *       get('variable', 'default')
     */
    public function getPath() {
    
        return $this->filename;
    
    }
    /**
     * Write a single row to the file
     *
     * @param array An array representing a row of data to be written
     * @access public
     */
    public function writeRow(Array $row) {
    
        $this->data[] = $row;
    
    }
    /**
     * Write multiple rows to file
     *
     * @param array An two-dimensional array representing rows of data to be written
     * @access public
     */
    public function writeRows($rows) {
    
        //if ($rows instanceof Csv_Writer) $rows->reset();
        foreach ($rows as $row) {
            $this->writeRow($row);
        }
    
    }
    /**
     * Writes the data to the csv file according to the dialect specified
     * This method is called by close()
     *
     * @access protected
     */
    protected function writeData() {
    
        $rows = array();
        foreach ($this->data as $row) {
            $rows[] = implode($this->formatRow($row), $this->dialect->delimiter);
        }
        $output = implode($rows, $this->dialect->lineterminator);
        fwrite($this->handle, $output);
    
    }
    /**
     * Accepts a row of data and returns it formatted according to $this->dialect
     * This method is called by writeData()
     * 
     * @param array An array of data to be formatted for output to the file
     * @access protected
     * @return array The formatted array (formatting determined by dialect)
     */
    protected function formatRow(Array $row) {
    
        foreach ($row as &$column) {
            switch($this->dialect->quoting) {
                case Csv_Dialect::QUOTE_NONE:
                    // do nothing... no quoting is happening here
                    break;                
                case Csv_Dialect::QUOTE_ALL:
                    $column = $this->quote($this->escape($column));
                    break;                
                case Csv_Dialect::QUOTE_NONNUMERIC:
                    if (preg_match("/[^0-9]/", $column))
                        $column = $this->quote($this->escape($column));
                    break;
                case Csv_Dialect::QUOTE_MINIMAL:
                default:
                    if ($this->containsSpecialChars($column)) 
                        $column = $this->quote($this->escape($column));
                    break;            
            }
        }
        return $row;
    
    }
    /**
     * Escapes a column (escapes quotechar with escapechar)
     *
     * @param string A single value to be escaped for output
     * @return string Escaped input value
     * @access protected
     */
    protected function escape($input) {
    
        return str_replace(
            $this->dialect->quotechar,
            $this->dialect->escapechar . $this->dialect->quotechar,
            $input
        );
    
    }
    /**
     * Quotes a column with quotechar
     *
     * @param string A single value to be quoted for output
     * @return string Quoted input value
     * @access protected
     */
    protected function quote($input) {
    
        return $this->dialect->quotechar . $input . $this->dialect->quotechar;
    
    }
    /**
     * Returns true if input contains quotechar, delimiter or any of the characters in lineterminator
     *
     * @param string A single value to be checked for special characters
     * @return boolean True if contains any special characters
     * @access protected
     */
    protected function containsSpecialChars($input) {
    
        $special_chars = str_split($this->dialect->lineterminator, 1);
        $special_chars[] = $this->dialect->quotechar;
        $special_chars[] = $this->dialect->delimiter;
        foreach ($special_chars as $char) {
            if (strpos($input, $char)) return true;
        }
    
    }
    /**
     * 
     * Closes out this file (can be called explicitly, but is called automatically by __destruct())
     *
     * @access public
     * @return null
     * @throws Csv_Exception_CannotAccessFile If unable to create or write to the file
     */
    public function close() {
    
        if (!is_resource($this->handle)) {
            $this->handle = @fopen($this->filename, 'wb');
        }
        
        if ($this->handle) {        
            $this->writeData();
            fclose($this->handle);
            $this->data = array(); // data has been written, so empty it
            return;        
        }
        // if parser reaches this, the file couldnt be created
        echo sprintf('Unable to create/access file: "%s".', $this->filename);
    
    }
    /**
     * When the object is destroyed, if there is still data waiting to be written to disk, write it
     *
     * @access public
     */
    public function __destruct() {
    
        if (!empty($this->data)) $this->close();
    
    }
}



class Csv_Dialect
{
    /**
     * Instructs Csv_Writer to quote only columns with special characters such as the
     * delimiter character, quote character or any of the characters in line terminator
     */
    const QUOTE_MINIMAL = 0;
    /**
     * Instructs Csv_Writer to quote all columns
     */
    const QUOTE_ALL = 1;
    /**
     * Instructs Csv_Writer to quote all columns that aren't numeric
     */
    const QUOTE_NONNUMERIC = 2;
    /**
     * Instructs Csv_Writer to quote no columns
     */
    const QUOTE_NONE = 3;
    /**
     * @var string The character used to seperate fields in a csv file
     */
    public $delimiter = ",";
    /**
     * @var string The character used to quote columns
     */
    public $quotechar = '"';
    /**
     * @var string The character used to escape the quotechar if it appears in a column
     */
    public $escapechar = "\"";
    /**
     * @var string This is a remnant of me copying functionality from python's csv module
     * @todo Implement this
     */
    public $doublequote;
    /**
     * @var string This is a remnant of me copying functionality from python's csv module
     * @todo Implement this
     */
    public $skipinitialspace;
    /**
     * @var boolean Set to true to ignore blank lines when reading
     */
    public $skipblanklines = true;
    /**
     * @var string The character(s) used to terminate a line in the csv file
     */
    public $lineterminator = "\r\n";
    /**
     * @var integer Set to any of the self::QUOTE_* constants above
     */
    public $quoting = self::QUOTE_ALL;
    
    public function __construct($options = null) {
    
        if (is_array($options)) {
            //pr($options);
            $properties = array();
            foreach ($this as $property => $value) $properties[$property] = $value;
            foreach (array_intersect_key($options, $properties) as $property => $value) {
                $this->{$property} = $value;
            }
        }
     
    }
}