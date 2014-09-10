<?php
	require_once  dirname(__FILE__) . '/../LVH-Fling/resources/php/aws/s3.php';
?>

<html>
	<head>
		<style media="screen" type="text/css">
		.progress {
			position: relative;
			width: 100%;
			height: 15px;
			background: #C7DA9F;
			border-radius: 10px;
			overflow: hidden;
		}

		.bar {
			position: absolute;
			top: 0;
			left: 0;
			width: 0;
			height: 15px;
			background: #85C220;
		}
		</style>
		
		<!-- Include Public Scripts -->
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		<script src="/javascript/jquery.fileupload.js"></script>	
		
		<!-- Include LVH-Fling Scripts -->
		<script src="../LVH-Fling/resources/config.js"></script>
		<script src="../LVH-Fling/resources/js/common.js"></script>
		<script src="../LVH-Fling/resources/js/aws/sqs.js"></script>
		
		<script type="text/javascript">	
		/******************************************************************************************************************
		* S3 Upload Process
		******************************************************************************************************************/	
		DEBUG_ENABLED = true;
		
		//Variables
		var localFileName = "";
		var s3FileName = "";
		var jobId = "";
		var s3Directory = "Jobs/";		
		
		//SQS Queues
		var sqsBaseUrl = 'https://sqs.us-east-1.amazonaws.com/293388242627/';
		var sqsJobsQueueName = 'LVH_VIChallenge_Jobs/';
		var jobsQueueUrl = sqsBaseUrl.concat(sqsJobsQueueName);
		
		$(document).ready
		( 
			function()
			{										
				$('.direct-upload').each( function() 
				{
					var form = $(this);

					$(this).fileupload(
					{
						url: form.attr('action'),
						type: 'POST',
						datatype: 'xml',
						
						add: function (event, data) 
						{	
							if(DEBUG_ENABLED == true)
							{
								alert("add");
							}
							
							//Store Values, We'll Need Them Later
							localFileName = data.files[0].name;
							jobId = UniqueId();
							s3FileName = jobId.concat(localFileName.replace(/\s+/g,""));
							
							//Check the file extension and set the S3 destination path of the form.
							var fileExtension = s3FileName.substr(s3FileName.lastIndexOf("."));
							
							//Check File Type
							if(fileExtension == ".vi" || fileExtension == ".zip")
							{					
								//Valid Extension, Set S3 Form Destination Path
								document.getElementById("s3UploadKey").value = s3Directory.concat(s3FileName);
							}
							else
							{
								alert("Please choose a file with .VI or .ZIP extension.");
							}	
						
							if(DEBUG_ENABLED == true)
							{
								alert("localFileName = " + localFileName);
								alert("s3FileName = " + s3FileName);
								alert("s3Directory = " + s3Directory);
							}
							
							// Use XHR, fallback to iframe
							options = $(this).fileupload('option');								
							use_xhr = !options.forceIframeTransport && ((!options.multipart && $.support.xhrFileUpload) || $.support.xhrFormDataFileUpload);
							if (!use_xhr) 
							{								
								using_iframe_transport = true;
							}
							
							//Submit File to S3
							data.submit();
						},
						
						send: function(e, data) 
						{
							if(DEBUG_ENABLED == true)
							{
								alert("send");
							}
						},
						
						progress: function(e, data)
						{
							if(DEBUG_ENABLED == true)
							{
								alert("progress");
							}
						},
						
						fail: function(e, data) 
						{
							if(DEBUG_ENABLED == true)
							{
								alert("fail");
								alert(e);
							}
						},
						success: function(data) 
						{
							if(DEBUG_ENABLED == true)
							{
								alert("success");
							}
							
							//Send Job Ready Message To Jobs SQS Queue
							SendSqsMessage(jobsQueueUrl, jobId);					
							
						},
						
						done: function (event, data) 
						{
							if(DEBUG_ENABLED == true)
							{
								alert("done");
							}
						}, 
					});
				});
			}
		);
		
		/******************************************************************************************************************
		* S3 Upload Helpers
		******************************************************************************************************************/	
						
		</script>
	</head>

	<body>
		<!-- Direct Upload to S3 -->		
		<?php
			S3UploadForm(LVH_VIChallenge);
		?>			

		<!-- Used to Track Upload within our App -->
		<form action="process-form-data.php" method="POST">
			<input type="hidden" name="upload_original_name" />
		</form>
	</body>
</html>