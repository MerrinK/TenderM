<?php

    class ControllerBase {

        var $dbc;

        # Constructor Method 

        function __constructor() {
            $this->dbc = new DBC();
        }
		// Write to Log
		function logWrite($data){
				$log = 'error-log.txt';
				//file_put_contents($log, date("D M d, Y G:i")."> ".$data."\n \n", FILE_APPEND | LOCK_EX);
		}

		public function isApiCall()
		{
			//print_r($_REQUEST);
			return  isset($_REQUEST["api_call"]) ? true : false;
		}
    }

?>
