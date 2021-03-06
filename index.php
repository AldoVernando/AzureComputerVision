<!DOCTYPE html>
<html>
<head>
    <title>Submission 2</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<style>
    table, th, td {
        border: 1px solid black;
    }
</style>
<body>
 
<script type="text/javascript">
    function processImage(x) {

        console.log(x);
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************
 
        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "f33c195d44c5495ab6f71d7079fc02b5";
 
        // You must use the same Azure region in your REST API method as you used to
        // get your subscription keys. For example, if you got your subscription keys
        // from the West US region, replace "westcentralus" in the URL
        // below with "westus".
        //
        // Free trial subscription keys are generated in the "westus" region.
        // If you use a free trial subscription key, you shouldn't need to change
        // this region.
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        document.querySelector("#sourceImage").src = x;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + x + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };

</script>
 
<h1>Image Detector :</h1>
Upload the image, then click the <strong>Upload image</strong> button.
<br><br>
Image to upload:
<form action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="fileUploaded" accept=".jpeg,.jpg,.png" required="">
	<input type="submit" name="submit" value="Upload Image">
</form>
<br><br>
<div id="wrapper" style="width:1020px; display:table;">
    <div id="jsonOutput" style="width:600px; display:table-cell;">
        Response:
        <br><br>
        <textarea id="responseTextArea" class="UIInput"
                  style="width:580px; height:400px;"></textarea>
    </div>
    <div id="imageDiv" style="width:420px; display:table-cell;">
        Source image:
        <br><br>
        <img id="sourceImage" width="400" />
    </div>
</div>
</body>
</html>

<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=practiceazurestorage;AccountKey=9dZUzz38/65GWTu2IlENFy7SKcZAG4e3lR9J8/rsC5ENveDZag0OVYdMiV9/c8rKteBGOOOMl/BVp57DUiIGGQ==";

$blobClient = BlobRestProxy::createBlobService($connectionString);

$containerName = "images";

        echo "<br />";

        if (isset($_POST['submit'])) {
            $file = strtolower($_FILES["fileUploaded"]["name"]);
            $content = fopen($_FILES["fileUploaded"]["tmp_name"], "r");
            $blobClient->createBlockBlob($containerName, $file, $content);
            header("Location: index.php");
        }
        
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix("");
        echo "These are the blobs present in the container: ";
        echo "<br>"; 
        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            ?>

            <table>
            <thead>
                <tr>
                    <td>Name</td>
                    <td>URL</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($result->getBlobs() as $blob)
            {
                ?>
                
                <tr>
                    <td><?php echo $blob->getName() ?></td>
                    <td><?php echo $blob->getUrl() ?></td>
                    <td><button name="submit" onclick="processImage('<?php echo $blob->getUrl() ?>')">Analyze Image</button></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            </table>

            <?php

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
        echo "<br />";
?>
