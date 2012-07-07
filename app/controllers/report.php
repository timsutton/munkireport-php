<?php
class report extends Controller
{
    function hash_check()
	{
		// Check if we have a serial and data
		if( ! isset($_POST['serial'])) die('Serial is missing');
		if( ! isset($_POST['data'])) die('Data is missing');

		require_once(APP_PATH . 'lib/CFPropertyList/CFPropertyList.php');
		
		// Create return object
		$out = new CFPropertyList();
		$out->add( $itemarr = new CFArray() );
		
		// Parse data
		$parser = new CFPropertyList();
		$parser->parse($_POST['data'], CFPropertyList::FORMAT_XML);
		
		// Get stored hashes from db
		$hash = new Hash();
		$hashes = $hash->all($_POST['serial']);
		
		// Compare sent hashes with stored hashes
		foreach($parser->toArray() as $key => $val)
		{
		    if( ! (isset($hashes[$key]) && $hashes[$key] == $val['hash']))
		    {
		        $itemarr->add( new CFString( $key ) );
		    }
		}
		
		// Return list of changed hashes
		echo $out->toXML();
	}
	
	function check_in()
	{
	    require_once(APP_PATH . 'lib/CFPropertyList/CFPropertyList.php');
		$parser = new CFPropertyList();
		$parser->parse($_POST['data'], CFPropertyList::FORMAT_XML);
		$arr = $parser->toArray();
		
		foreach($arr as $key => $val)
		{
		    // Skip items without data
		    if ( ! isset($val['file'])) continue;
		    
		    // Todo: prevent admin and user models, sanitize $key
		    if(file_exists(APP_PATH . 'models/' . $key . '.php'))
		    {
		        // Load model
		        $class = new $key($_POST['serial']);
		        // Process data
		        $class->process($val['file']);
		        // Store hash
		        $hash = new Hash($_POST['serial'], $key);
		        $hash->hash = $val['hash'];
		        $hash->save();
        		
		    }
		    else
		    {
		        printf("Model not found: %s\n", $key);
		    }
		}
	}
}	