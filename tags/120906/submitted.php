<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$s = $startup->session;
/*if($s->getUserId()!=""){
	if($s->getCurrentItem()==""){
		Header("location: http://".$startup->settings['url']."code.php");
	}
	else{
		Header("location: http://".$startup->settings['url']."design.php");
	}
}*/
include "preamble.php";	
?>
	<div id="blank">          
        <h2>
           <span>Confirm Your Design</span>
       </h2>
	</div>
	<section id="submitted_box">
        <p>
            Your order has been sent to manufacturing and a confirmation email has been sent to you. Thank you for using Create Your Own (CYO).
        </p> 
        <div>
        	<a href="code.php">Home Page</a>
        </div>   	
     </section>
<?php
include "postamble.php";
?>