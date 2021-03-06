<?php
	/*
		UserCake Version: 1.4
		http://usercake.com
		
		Developed by: Adam Davis
	*/
	require_once("models/config.php");
	
	//Prevent the user visiting the logged in page if he/she is not logged in
	if (!isUserLoggedIn()) { header("Location: login.php"); die(); }
	if (!$loggedInUser->isGroupMember(2)) { die(); }
	
	if (!isset($_GET['task']) || !isset($_GET['query'])) 
	{
		header("Location: admin.assignments.php"); die();
	}
	
	$tid = $_GET['task'];
	$query = $_GET['query'];
	$task = getTask($tid);

	$candidates = adminGetCandidates($loggedInUser, $tid, $query);	
	$state = array();
	foreach ($candidates as $c) {
		$state[$c['result_Candidate']] = array("b"=>$c['result_Broad'], "f"=>$c['result_Fine']);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MIREX :: E6K :: ADMIN <?php echo $loggedInUser->display_username; ?></title>
<link href="mirex.css" rel="stylesheet" type="text/css" />
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript">
var state = <?php echo json_encode($state);?>

colorRow = function(candidate) 
{
	if ((state[candidate]['b'] != "") && (state[candidate]['f'] > -1)) {
		$("#row-"+candidate+" > td").css("background-color", "#9c6");		
	}
}

JSON = {
  encode : function(input) {
    if (!input) return '""'
    switch (input.constructor) {
      case String: return '"' + input + '"'
      case Number: return input.toString()
      case Array :
        var buf = []
        for (i in input)
          buf.push(JSON.encode(input[i]))
            return '[' + buf.join(', ') + ']'
      case Object:
        var buf = []
        for (k in input)
          buf.push(k + ' : ' + JSON.encode(input[k]))
            return '{ ' + buf.join(', ') + '} '
      default:
        return 'null'
    }
  }
}
</script>
</head> 
 
<body> 
<div id="wrapper">

	<div id="content">
    
        <div id="left-nav">
        <?php include("layout_inc/left-nav.php"); ?>
            <div class="clear"></div>
        </div>
        
        <div id="main">
        <h1><?php echo stripslashes($task['task_Name']);?></h1>
        <h2>Query <?php echo enhash($query);?></h2>
        <?php
        if ($task['task_Instructions'] != '') {
        ?>
		<div class="alert">
			<strong>Instructions</strong>
			<p>
			<?php echo stripslashes($task['task_Instructions']);?>
			</p>
		</div>
		<?php
		}
		?>
		<table cellspacing="0px" cellpadding="5px">
			<tbody>
				<tr>
					<th>Listen</th>
					<th>Categorical Similarity</th>
					<th>Similarity</th>
				</tr>
				<tr>
					<td>Same query for all candidates</td>
					<td>
						<table width="175px">
							<tr align="center">
								<td>Not Similar</td>
								<td>Somewhat Similar</td>
								<td>Very Similar</td>
							</tr>
						</table>
					</td>
					<td>(0: Low) to (100: High)</td>
				</tr>
        <?php
        	foreach ($candidates as $c) 
        	{
        		?>
        			<tr id="row-<?php echo $c['result_Candidate'];?>">
						<td>
							<div style="font-weight:bold"><?php echo enhash($c['result_Candidate'], "beer");?></div>
							<a type="audio/mpeg" href="<?php echo genMP3URL($task['task_MP3'], $query, $c['result_Candidate']);?>">Query</a><br/>
							<a type="audio/mpeg" href="<?php echo genMP3URL($task['task_MP3'], $c['result_Candidate'], NULL);?>">Candidate</a>
						</td>
						<td align="center">
							<table width="175px">
								<tr align="center">
									<?php
									$bs = array("NS", "SS", "VS");
									foreach ($bs as $b) 
									{
										?>
										<td>
											<?php echo $b;?>
											<input 	type="radio" 
													value="<?php echo $b;?>" 
													name="broad-<?php echo $c['result_Candidate'];?>" 
													disabled='true'
													<?php echo ($c['result_Broad'] == $b) ? "checked='true'" : "";?>
													/>
										</td>
										<?php
									}
									?>
									</tr>
							</table>
						</td>
						<td>
							<table>
								<tr>
									<td><div style="width:130px;margin-bottom:10px;margin-top:10px;" id="slider-<?php echo $c['result_Candidate'];?>"></div></td>
									<td><input disabled='true' type="text" size="2" id="fine-<?php echo $c['result_Candidate'];?>" value="<?php echo ($c['result_Fine'] >= 0 ? $c['result_Fine'] : 0);?>"/></td></tr>
							</table>
						</td>
					</tr>
		<?php
        	}        
        ?>
		</table>
		<script type="text/javascript">
			$(document).ready(function() {
			<?php
        	foreach ($candidates as $c) 
			{
			?>$("#slider-<?php echo $c['result_Candidate'];?>").slider({ 
				min: 0, max: 100, step: 1,
				disabled: true,
				value: <?php echo ($c['result_Fine'] >= 0 ? $c['result_Fine'] : -1);?>,
				stop: function(event, ui) { 
					var x = $("#fine-<?php echo $c['result_Candidate'];?>");
					x[0].value=ui.value;
				}
			    });
				<?php
				if (($c['result_Broad'] != '') && ($c['result_Fine'] > -1)) {
				?>
					$("#row-<?php echo $c['result_Candidate'];?> > td").css("background-color", "#9c6");
				<?php
				}
			}
			?>
			});
		</script>
		<script type="text/javascript">
		var YMPParams = { displaystate: 3, autoadvance: false }
		</script>
		<script type="text/javascript" src="http://mediaplayer.yahoo.com/latest"></script>
		<div>
			<h2>Finish</h2>
			<p>Quickly scan the candidates above, all rows should be green, indicating that
			you've successfully completed each evaluation. Your similarity judgments
			are saved automatically when you make them. If you are afraid that the
			system has not logged any of your judgments properly, reload this page
			in your browser and check all the results.</p>
		</div>
		</div>  
	</div>
</div>
</body>
</html>

