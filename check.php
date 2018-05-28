<?php

//usleep(500000);

// load information about seats
$map = array();

if (($handle = fopen('theatre.csv', 'r')) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE)
	{
		$key = 'seat-'.$data[2].'-'.$data[3].'-'.$data[4];

        $map[$key] = array(
			'id'		=> $data[0],
			'sector'	=> $data[1],
			'sector_id'=> $data[2],
			'row'		=> $data[3],
			'number'	=> $data[4],
			'price_full'	=> $data[5],
			'price_reduced'	=> $data[6],
			'status'	=> $data[7],
			'selected'	=> $data[8]
		);
    }
    fclose($handle);
}

// answer is always positive
$id = $_REQUEST['id'];
$ret = $map[$id];
$ret['result'] = 1;

/*
$rand = mt_rand(1,100);

if($rand<=10)
	$ret['result'] = 0;
else
	$ret['result'] = 1;
*/

echo json_encode($ret);
