<?
/**
 * <b>DatabaseDriver</b><br /><i>A standalone CodeIgniter "ActiveRecord" inspired set of query handling and manipulation.
 * @author Kyle Harrison &lt;kyle@blackjaguarstudios.com&gt;
*/
class DatabaseDriver {
	
	private $mode; // select, insert, update, delete
	private $select;
	private $from = array();
	private $where = array();
	private $join = array();
	private $set = array();
	private $orderby;
	private $limit;

	private $driver;
	
	private $active_row = 0; // Determins which result in the results array is currently active

	public $query;
	public $results;
	
	
	
	public function __construct($engine = "mysql") {
		$this->driver = $engine;
	}
	
	public function select($cols = "*") {
		
		$this->mode = "select";
	
		if(is_array($cols)) {
			foreach($cols as &$c) $c = "`".$c."`";
			$cols = implode(", ", $cols);
		} else {
			
		}
		
		$this->select = $cols;
		
		$this->build_query();
		return $this;
	}
	
	public function insert($table = null, $set = null) {
		$this->mode = "insert";
		
		if($table != null) $this->from($table);
		
		if($set != null) $this->set($set);
		
		$this->build_query();
		return $this;
	}
	
	public function update($table = null, $set = null) {
		$this->mode = "update";
		
		if($table != null) $this->from($table);
		
		if($set != null) $this->set($set);
		
		$this->build_query();
		return $this;
	}
	
	public function delete($table = null, $set = null) {
		$this->mode = "delete";
		
		if($table != null) $this->from($table);
		
		if($set != null) $this->set($set);
		
		$this->build_query();
		return $this;
	}
	
	public function from($table, $alias = null) {
		$this->from[] = "`$table`".(($alias != null)?" as `$alias`":"");
		
		$this->build_query();
		return $this;
	}
	
	// From alias
	public function table($table, $alias = null) {
		$this->from($table, $alias);
		return $this;
	}
	
	// From alias
	public function into($table, $alias = null) {
		$this->from($table, $alias);
		return $this;
	}
	
	// parses a list of setable objects
	public function set($data, $value = null, $escape = true) {
		if(is_array($data)) {
			foreach($data as $k=>$d) {
				$this->set[] = (($escape == true) ? "`".$k."` = '".$d."'" : "`".$k."` = ".$d );
			}
			$this->build_query();
			return $this;
		}
		
		$this->set[] = (($escape == true) ? "`".$data."` = '".$value."'" : "`".$data."` = ".$value );
		$this->build_query();
		return $this;
	}
	
	public function where($col, $clause) {
		$this->where[] = "`$col` = `$clause`";
		
		$this->build_query();
		return $this;
	}
	
	public function join($table, $alias, $on_original, $on_compare, $type = "left") {
		
		switch($type) {
			default:
			case "left":
				$type = "LEFT JOIN";
			break;
			case "right":
				$type = "RIGHT JOIN";
			break;
			case "inner":
				$type = "INNER JOIN";
			break;
			case "outer":
				$type = "OUTER JOIN";
			break;
		}
		
		if($alias != "" && $alias != null) $alias = "as `$alias`";
		
		$on = "ON `$on_original` = `$on_compare`";
		
		$this->join[] = "$type `$table` $alias $on";
	
		$this->build_query();
		return $this;
	}
	
	public function orderby($col, $dir = null) {
		$data = explode(" ", $col);
		if(count($data)>0) {
			$col = "`".$data[0]."` ".$data[1];
		}
		
		$order = $col;
		
		if(trim($order) != "" && $dir == null) $dir = "ASC";
		
		if($dir != null) $order .= "$dir";
		
		$this->orderby = $order;
		
		$this->build_query();
		return this;
	}
	
	 // Orderby Alias
	public function order($col, $dir = null) {
		$this->orderby($col, $dir);
	}
	
	public function build_query() {
		$query = array();
		
		switch($this->mode) {
			case "select":
			default:
				
				$query[] = "SELECT ".$this->select;
		
				if(count($this->from) >= 1)
					$query[] = "FROM ".implode(" AND ", $this->from);
				
				if(count($this->join) >= 1)
					$query[] = implode(" ", $this->join);
				
				if(count($this->where) >= 1)
					$query[] = "WHERE " . implode(" AND ", $this->where);
					
				if($this->orderby != '')
					$query[] = "ORDER BY " . $this->orderby;
				
				
				break;
			case "insert":
				$query[] = "INSERT INTO " .$this->from[0]. " SET";
				$query[] = implode(", ", $this->set);
				
				break;
			case "delete":
				$query[] = "DELETE FROM ".$this->from[0];
				if(count($this->where) >= 1)
					$query[] = "WHERE " . implode(" AND ", $this->where);
					
				break;
			case "update":
				$query[] = "UPDATE " .$this->from[0]. " SET" ;
				$query[] = implode(", ", $this->set);
				if(count($this->where) >= 1)
					$query[] = "WHERE " . implode(" AND ", $this->where);
				break;
		}
		
	
		$this->query = implode(" ", $query);
		
		return $this;
	}
	
	// Executes the built query
	public function exec() {
		if($sql = @mysql_query($this->query)) {
			$fetch = mysql_fetch_object($sql);
			$this->results = $fetch;
			return $this;
		} else {
			die("<span style='color: red'>There was an error running your query:</span><br />". mysql_error());
		}
	}
	
	// Alias for exec()
	public function get() {
		$this->exec();
		return $this;
	}
	
	// Alias for exec()
	public function run() {
		$this->exec();
		return $this;
	}
	
	// Returns all results
	public function results() {
		return $this->results();
	}
	
	// gets specific row index. if no number is provided gets the top result
	public function result($row = null) {
		return $this->results[$row];
	}
	
	// alias for "result"
	public function row($row = null) {
		return $this->result($row);
	}
	
	// get first NUM available rows
	public function rows($rows = 1) {
		
	}
	
	public function next() {
		$active = $this->active_row;
		if((count($this->results) - 1) >= ($active + 1)) {
			$this->active_row++;
		}
		
		return $this->row($this->active_row);
	}
	
	public function prev() {
		$active = $this->active_row;
		if((count($this->results) - 1) >= ($active - 1)) {
			$this->active_row--;
		}
		
		return $this->row($this->active_row);
	}
	
	
	// alias for next()
	public function step() {
		return $this->next();
	}
	
	// alias for prev()
	public function back() {
		return $this->prev();
	}
	
	// Resets the driver
	public function flush() {
		
		$this->select = null;
		$this->from = array();
		$this->where = array();
		$this->join = array();
		$this->set = array();
		$this->orderby = null;
		$this->limit = null;
		$this->active_row = 0;
		$this->query = null;
		$this->results = null;
		
		return $this;
	}
	
	// alias for flush()
	public function reset() {
		$this->flush();
		return $this;
	}
	
}

$db = new DatabaseDriver("mysql");




/*
Example of the moment: 

// Inserting Data
$db->insert("test", array(
	"col1"=>"data1",
        "col2"=>"data2"
));
$db->where("col1", "data2");
$db->set("date_updated", "NOW()", false);
$result = $db->run()->results();

var_dump($db);
*/