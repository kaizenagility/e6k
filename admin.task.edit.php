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

	if (!empty($_POST)) 
	{
		$name = $_POST['task_Name'];
		$size = $_POST['task_Assignment_Size'];
		$url  = $_POST['task_MP3'];
		$inst = $_POST['task_Instructions'];
		
		if ($name == '') { $name = date('Y') . " New Task"; }
		if (!preg_match("/^[0-9]+$/", $size)) { $size = 3; }
		if ($url == '') { $url = 'http://www.music-ir.org/mirex/e6k/audio/'; }
		
		if (substr($url, -1) != "/") { $url = $url . "/"; }

		if (!isset($_POST['task_ID']))
		{
			adminCreateTask($loggedInUser, $name, $size, $url, $inst);
			$result = "created";
		}
		else 
		{
			$tid = $_POST['task_ID'];			
			adminUpdateTask($loggedInUser, $tid, $name, $size, $url, $inst);
			$result = "updated";
		}
		header("Location: admin.task.edit.php?" . $result); die();
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MIREX :: E6K :: <?php echo $loggedInUser->display_username; ?></title>
<link href="mirex.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="wrapper">

	<div id="content">
    
        <div id="left-nav">
        <?php include("layout_inc/left-nav.php"); ?>
            <div class="clear"></div>
        </div>
        
        <div id="main">
			<?php
			if (isset($_GET['updated'])) {
				?>
				<div id="success">Task Updated</div>
				<?php
			}
			elseif (isset($_GET['created'])) {
				?>
				<div id="success">Task Created</div>
				<?php
			}
			?>
			<h1>Current Tasks</h1>
            <?php
			$tasks = getTasks();
			if (count($tasks) > 0) {
				foreach ($tasks as $tid=>$task) 
				{
					?>
						<h3><?php echo $task['task_Name'];?></h3>
						<div>
							<form action="admin.task.edit.php" method="post">
								<input type="hidden" name="task_ID" value="<?php echo $task['task_ID'];?>"/>
								<div>
									<label>Task Name</label>
									<input type="text" name="task_Name" value="<?php echo $task['task_Name'];?>" size="30"/>
								</div>
								<div>
									<label>Assignment Size</label>
									<input type="text" name="task_Assignment_Size" value="<?php echo $task['task_Assignment_Size'];?>" size="3"/> queries per grader.
								</div>
								<div>
									<label>MP3 Base URL</label>
									<input type="text" name="task_MP3" value="<?php echo $task['task_MP3'];?>" size="45"/>
								</div>
								<div>
									<label>Task Instructions</label>
									<textarea name="task_Instructions" style="width:350px;height:100px;"><?php echo stripslashes($task['task_Instructions']);?></textarea>
								</div>
								<div>
									<label>Data</label>
									<?php
										$n = adminIsTaskDefined($loggedInUser, $tid);
										if ($n > 0) 
										{
											?><span><?php echo $n;?> results loaded</span><?php
										}
										?>
										<input type="button" onclick="window.location.href='admin.results.load.php?task=<?php echo $tid?>'" value="Load Data" />
										<input type="button" onclick="window.location.href='admin.report.download.php?task=<?php echo $tid?>'" value="Generate Report"/><?php
									?>
								</div>
								<input type="submit" name="submit" value="Edit Task"/>
							</form>							
						</div>
					<?php
				}
			}
			else {
				?>
				<p>No Current Tasks</p>
			<?php
			}
   			?>
   			<h2>Create New Task</h2>
			<form action="admin.task.edit.php" method="post">
				<div>
					<label>Task Name</label>
					<input type="text" name="task_Name" value="<?php echo date('Y');?> New Task"/>
				</div>
				<div>
					<label>Assignment Size</label>
					<input type="text" name="task_Assignment_Size" value="3" size="3"/> queries per grader.
				</div>
				<div>
					<label>MP3 Base URL</label>
					<input type="text" name="task_MP3" value="http://www.music-ir.org/mirex/e6k/audio/" size="45"/>
				</div>
				<div>
					<label>Task Instructions</label>
					<textarea name="task_Instructions" style="width:350px;height:100px;"></textarea>
				</div>
				<input type="submit" name="submit" value="Create Task"/>
			</form>
   			
  		</div>  
	</div>
</div>
</body>
</html>

