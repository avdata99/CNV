<?php

/**
 * subir el csv para limpiar registros y definir cantidades de secuestros
 */


if ( isset($_POST["submit"]) ) {

   if ( isset($_FILES["file"])) {

            //if there wa"s an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }
        else {
                 //Print file details
             echo "Upload: " . $_FILES["file"]["name"] . "<br />";
             echo "Type: " . $_FILES["file"]["type"] . "<br />";
             echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
             echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

             $dest = $_FILES["file"]["name"];
             echo "<br/>TMP $dest";
             if (file_exists("uploads/$dest")) 
                 {
                 $dest .= "_" . rand(10000,99999);
                 }
            
            $f = file_get_contents($_FILES["file"]["tmp_name"]);
            //echo "file: " . $f;
            
            $res = array();
            $filas = explode("\n", $f);
            foreach ($filas as $fila) 
                {
                $data = explode(",", $fila);
                
                $newemp = array(
                    "fecha_secuestro"=>$data[2]
                    ,"fecha_liberacion"=>$data[3]    
                     );
                
                if ($newemp["fecha_secuestro"] != "")
                    {
                    if (strstr($newemp["fecha_secuestro"], "/"))
                        {
                        $d2 = explode("/", $newemp["fecha_secuestro"]);
                        //validar que sea fecha mejor TODO
                        if (strstr($d2[1],"-")) $res[$data[1]." (bis)"] = array("fecha_secuestro"=>trim($d2[1]));
                        $newemp["fecha_secuestro"] = trim($d2[0]);
                        }
                    //validar que sea fecha mejor TODO
                    if (strstr($newemp["fecha_secuestro"],"-")) $res[$data[1]] = $newemp;
                    }
                
                }
                 
        //echo "<pre>".print_r($res, true)."</pre>";    
        $fechasFinales=array();
        foreach ($res as $d) 
            {
            $p =  explode("-", $d["fecha_secuestro"]);
            if (!isset($fechasFinales[$p[0]."-".$p[1]])) $fechasFinales[$p[0]."-".$p[1]] = 1;
            else $fechasFinales[$p[0]."-".$p[1]] = $fechasFinales[$p[0]."-".$p[1]] +1;
            }
        //validar y escribir el archivo de resultados
        $res = json_encode($fechasFinales);
        
        $f = fopen("uploads/fechas.json", "w");
        $g = fwrite($f,$res);
        $h = fclose($f);
        }
     } else {
             echo "No file selected <br />";
     }
}


?>
<form method="post" enctype="multipart/form-data">

<td width="80%"><input type="file" name="file" id="file" /></td>
<input type="submit" name="submit" />

</form>