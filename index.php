<!DOCTYPE html>
<html>
<head>
    <title>Analyze Sample</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
 
<script type="text/javascript">
    function processImage() {
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************
 
        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "252907e3c87f4dcf94c7441179b5c554";
 
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
        var sourceImageUrl = document.getElementById("inputImage").value;
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
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
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
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
 
<h1>Analyze image:</h1>
Enter the URL to an image, then click the <strong>Analyze image</strong> button.
<br><br>
Image to analyze:
<form action="index.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="fileUploaded" accept=".jpg,.jpeg,.png">
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

$connectionString = "DefaultEndpointsProtocol=https;AccountName=submission;AccountKey=m7qQe3NPr50h0VEdckYmQXo6rFBHTmU50g4yan9Aq+Ye+Gqg7tsUR/w1CPOJLUKlnNsz+bQ9sa/mEOxT29BLig==";
// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);
// if (!isset($_GET["Cleanup"])) {
    // Create container options object.
    // $createContainerOptions = new CreateContainerOptions();
    
    // $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    // // Set container metadata.
    // $createContainerOptions->addMetaData("key1", "value1");
    // $createContainerOptions->addMetaData("key2", "value2");
    $containerName = "blockblobs";
        // Create container.
        // $blobClient->createContainer($containerName, $createContainerOptions);
        // Getting local file so that we can upload it to Azure.
        // $myfile = fopen($fileToUpload, "w") or die("Unable to open file!");
        // fclose($myfile);
        
        # Upload file as a block blob
        // echo "Uploading BlockBlob: ".PHP_EOL;
        // echo $fileToUpload;
        echo "<br />";
        
        // $content = fopen($fileToUpload, "r");
        //Upload blob
        if (isset($_POST['submit'])) {
            $fileToUpload = strtolower($_FILES["fileUploaded"]["name"]);
            $content = fopen($_FILES["fileUploaded"]["name"], "r");
            $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        }
//         $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
//         // List blobs.
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
                    <td><input type="submit" name="submit" value="Analyze Image"></td>
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
