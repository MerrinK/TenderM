<?php

class InsertTables {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}

	public function Insert(){

		$sql= "
		DROP TABLE IF EXISTS `material_sub_type`;
		CREATE TABLE `material_sub_type` (
		  `id` int(11) NOT NULL,
		  `material_type_id` int(11) NOT NULL,
		  `name` varchar(20) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

		INSERT INTO `material_sub_type` (`id`, `material_type_id`, `name`) VALUES
		(1, 1, 'abc'),
		(2, 1, 'def'),
		(3, 1, 'hij'),
		(4, 2, 'klmn'),
		(5, 2, 'opq'),
		(6, 2, 'rst'),
		(7, 3, 'uvw'),
		(8, 3, 'xyz'),
		(9, 3, 'zyx');

		ALTER TABLE `material_sub_type`
		  ADD PRIMARY KEY (`id`)";
		$result = $this->dbc->_query($sql);


		$sql2="SELECT * FROM material_sub_type ";
		$result = $this->dbc->get_result($sql2);
		print_r($result);
	}



}
?>



