<html>
<head>
    <title>GetIntTool</title>
    <link rel="stylesheet" href="sakura.css">
    <style>
        .inputs_wrapper {
            display: flex;
        }
        .inputs_wrapper > div {
            margin-right: 5px;
        }
        #title {
            margin: 30px;
            text-align: center;
        }
    </style>
</head>
<body>

<h1 id="title">GetIntTool</h1>
<form method="post" action="">
    <div>
        <label for="advertiser_id">Advertiser all campaigns info:</label>
        <input placeholder="Advertiser ID" value="<?php echo Helper::getFieldValue('advertiser_id') ?>" id="advertiser_id" name="advertiser_id" type="text">
        <button name="advertiser" type="submit">Get advertiser info</button>
    </div>

    <div class="inputs_wrapper">
        <div>
            <label for="advertiser_id2">Campaign info:</label>
            <input placeholder="Advertiser ID" id="advertiser_id2" value="<?php echo Helper::getFieldValue('advertiser_id2') ?>" name="advertiser_id2" type="text">
        </div>
        <div>
            <label for="campaign_id">&nbsp;</label>
            <input placeholder="Campaign ID" id="campaign_id" value="<?php echo Helper::getFieldValue('campaign_id') ?>" name="campaign_id" type="text">
            <button name="campaign" type="submit">Get campaign info</button>
        </div>
    </div>

    <div class="inputs_wrapper">
        <div>
            <label for="advertiser_id3">Convert targeting:</label>
            <input placeholder="Advertiser ID" id="advertiser_id3" value="<?php echo Helper::getFieldValue('advertiser_id3') ?>" name="advertiser_id3" type="text">
        </div>
        <div>
            <label for="campaign_id2">&nbsp;</label>
            <input placeholder="Campaign ID" id="campaign_id2" value="<?php echo Helper::getFieldValue('campaign_id2') ?>" name="campaign_id2" type="text">
            <button name="campaign_to_ip_range" type="submit">Targeting -> IP</button>
            <button name="campaign_to_telco" type="submit">Targeting -> Telco</button>
        </div>
    </div>

</form>

<pre><code><?php print_r($core->getOutput()); ?></code></pre>
<pre><code><?php print_r($core->getInfo()); ?></code></pre>

</body>
</html>