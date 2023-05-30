<?php
class DBConnection extends mysqli{
  //Create DB connection
  private $intFields = array("display","custom","StatusOn");

  function __construct($host=DB_HOST,$dbUser=DB_USER,$dbPass=DB_PASS,$dbName=DB_NAME) {
    
    @parent::__construct($host,$dbUser,$dbPass,$dbName);
    if (mysqli_connect_error()){
      die("Failed to connect to MySQL: " .mysqli_connect_error());
    }
  }

  public function get_rows($sql){
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
  }
  public function update_row($sql){
        $result = $this->query($sql);
        $RowDetail=array("Insert_Id"=> $this->insert_id,"AffectedRows"=>$this->affected_rows);
        return $RowDetail;  
  }
  // public function update_row($sql){
  //       $result = $this->query($sql);
  //       return $this->insert_id;
  // }

  // public function insert_row($sql){
  //       $result = $this->query($sql);
  //       return $this->insert_id;
  // }


  // public function inserted_row($sql){
  //       $result = $this->query($sql);
  //       $RowDetail=array($this->affected_rows, $this->insert_id);
  //       return $RowDetail;      
  // }

  

  public function Create_Insert_Query($table, $data){
       
        $query = 'INSERT INTO ' . $table . ' (';
                while (list($columns, ) = each($data)) {
                                $query .= $columns . ', ';
                }

        $query = substr($query, 0, -2) . ') VALUES (';
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


        $this->query($query);
        $RowDetail=array("Insert_Id"=> $this->insert_id,"AffectedRows"=>$this->affected_rows);

        return $RowDetail;
  }

  public function Create_Update_Query($table, $data, $whereFields){
        if (is_array($whereFields)){
            while(list($idn,$idv)=each($whereFields))
            {
                if( in_array($idn, $this->intFields)) $where[] = $idn."= $idv";
                else $where[] = $idn."='$idv'";
            }
        }else{
            $where[] = "$whereFields";
        }

        while(list($k,$v)=each($data)){
                if( in_array($k, $this->intFields)) $to[] = $k."=$v";
                else $to[] = $k."='$v'";
        }

        $sql = "UPDATE $table SET ".implode(',',$to)." WHERE ".implode(" AND ",$where);
        $this->query($sql);
        $RowDetail=array("Insert_Id"=> $this->insert_id, "AffectedRows"=>$this->affected_rows);

        return $RowDetail;
  }


}
?>
