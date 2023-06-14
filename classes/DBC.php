<?php
class DBC extends mysqli
{
    private $queryresult;
    private $intFields = array("display","custom","StatusOn");

    //Create DB connection
    function __construct($host=DB_HOST,$dbUser=DB_USER,$dbPass=DB_PASS,$dbName=DB_NAME){ 
	
            parent::__construct($host,$dbUser,$dbPass,$dbName);
            //Connection is possible
            if (mysqli_connect_error())
            {
                    //die("Failed to connect to MySQL: " .mysqli_connect_error());
                    system("echo 'MySQL IS DOWN'| mail -s 'MYSQL IS DOWN - CRITICAL' operations@planitwith.me");
                    header('Location: 500.php');
            }
            //$resp=$this->query("SET SESSION time_zone = '".TIMEZONE_KEY."'");
    }

    // All queries should be prepared and executed to ensure no SQL injection happens.
    public function update($query)
    {
            $error="";
            $res=0;
            if( $stmt = $this->prepare($query))	{
                    try	{
                            $stmt->execute();
                            $res=$stmt->affected_rows;
                            $stmt->close();
                    }
                    catch(Exception $ex)	{
                            $stmt->close();
                            throw $ex;
                    }
                    return $res;
            }
            throw new Exception("Query seems to be erroneous or malicious => ".$query);
    }
	
	function _createInsertQuery($data,$table)
	{
		$query = 'insert  into ' . $table . ' (';
                while (list($columns, ) = each($data)) {
                                $query .= $columns . ', ';
                }

        $query = substr($query, 0, -2) . ') values (';
        reset($data);
       // echo $query."\n";
        while (list(, $value) = each($data)) 
        {
            switch ((string)$value) {
                case 'now()':
                $query .= 'now(), ';
                break;

                case 'null':
                $query .= 'null, ';
                break;

                default:
                $query .= '\'' . addslashes($value) . '\', ';
                break;
            }
        }
        $query = substr($query, 0, -2) . ')';
        return $query;
	}

    function insert_query($data, $table){
        //CommonFunction::addAccessLog($this,"DBC::insert_query","MODIFY",$table,json_encode($data));
        $query=$this->_createInsertQuery($data,$table);
        //echo $query;
        $this->queryresult = $this->update($query);
        if ($this->queryresult) { return true; } else { return false; }
    }

    function update_query($mas, $table, $whereFields)
    {
        if (is_array($whereFields))
        {
            while(list($idn,$idv)=each($whereFields))
            {
                if( in_array($idn, $this->intFields)) $where[] = $idn."= $idv";
                else $where[] = $idn."='$idv'";
            }
        }
        else
        {
            $where[] = "$whereFields";
        }

        while(list($k,$v)=each($mas))
        {
                if( in_array($k, $this->intFields)) $to[] = $k."=$v";
                else $to[] = $k."='$v'";
        }

        $sql = "UPDATE $table SET ".implode(',',$to)." WHERE ".implode(" AND ",$where);
        // echo $sql;
		//CommonFunction::addAccessLog($this,"DBC::update_query","MODIFY",$table,json_encode($sql));
		//echo $sql;
        return $this->query($sql);
    }

	function _query($sql)
	{
		return parent::query($sql);
	}
	
	function query($sql,$resultMode=NULL)
	{
		if(strpos("select",strtolower($sql)) != '0')	CommonFunction::addAccessLog($this,"DBC::query","MODIFY","",json_encode($sql));
		return parent::query($sql,$resultMode);
	}
    function get_array($sql='')
    {
        if ($sql) {
                        $this->queryresult = $this->query($sql);
                        if ($this->queryresult) return $this->queryresult->fetch_assoc();
        }
        return array();
    }       

    function free_result()
    {
        return $this->queryresult->free();
    }

    function get_result( $sql = '' )
    {

        if ($sql) { 
			//echo "\n\n\n >>>>>> $sql";
			$this->queryresult=$this->query($sql);
			$c = 0;
			$res = array();
			if( $this->queryresult ){
				while ($row = $this->queryresult->fetch_assoc())
				{
						$res[$c] = $row;
						$c++;
				}
				$this->free_result();
			}
		}
        return $res;
    }

    function get_single_result( $sql = '',$col='')
    {
        if ($sql) 
        { 
            $this->queryresult=$this->query($sql);
            if ($this->queryresult->num_rows>0)
            {
                $row = $this->queryresult->fetch_assoc();
                $this->free_result();
                return @implode("",$row);
            }
            else return false;
        }
        else{
            return false;
        }
    }
    
    function get_result_array( $sql = '' )
    {
        //echo "########################DBC : $sql \n";
        $res = array();
        if ($sql)
        {
            $this->queryresult=$this->query($sql);
			$c = 0;
			//if ( !$this->queryresult) return $res;
			if ( $this->queryresult)	{
			while ($row = $this->queryresult->fetch_array())
			{
				$res[$c] = $row;
				$c++;
			}
			$this->free_result();
			}
        }
        return $res;
    }
    
    function get_assoc_array( $sql = '' )
    {
        $res = array();
        if ($sql)
        {
            $this->queryresult=$this->query($sql);
			$c = 0;
			while ($row = $this->queryresult->fetch_assoc())
			{
				$res[$c] = $row;
				$c++;
			}
			$this->free_result();
		}
        return $res;
    }

    function get_double_array( $sql = '' )
    {
		$res = array();
        if ($sql) { 
			$this->queryresult=$this->query($sql);
			while ($row = $this->queryresult->fetch_array())
			{
				$res[$row[0]] = $row[1];
			}
			$this->free_result();
		}
        return $res;
     }
     
     function excSql( $sql = '' )
    {
        if ($sql) { 
            return $this->query($sql);
        }
        return FALSE;
     }
     
    /** Escape string used in sql query */
    function sql_escape($msg)
    {
		return $this->real_escape_string($msg);
    }
    
    /*is query result set empty ?*/        
    function is_empty($sql = '')
    {
        if ($sql) { $this->queryresult=$this->query($sql); }
        if ($this->queryresult && $this->queryresult->num_rows>0)	return true;
        else return false;
    }

    /*is query result set valid ?*/
    function not_empty($sql = '')
    {
        if ($sql) { $this->queryresult=$this->query($sql); }
        if ($this->queryresult && 0 == $this->queryresult->num_rows>0)
        {
                return false;
        }
        else
        {
                return true;
        }
    }
    
    function get_insert_id()
    {
        return $this->insert_id;
    }


    

}
?>
