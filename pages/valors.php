<?php
    $mos = $ms->searchMediaByValue($mltc);
    if ( ! empty($mos) && $ms->checkIsValidJSON($mos)):
        $s = json_decode($mos,TRUE);
        if (isset($s) && is_array($s)):
            $brdTitle = ($s["success"] !== FALSE OR ($s["success"] === FALSE && array_key_exists("title",$s) !== FALSE)) ? $s["title"] : "Errore!";
?>

<span id="dataMedia" class="d-none"><?= $brdTitle; ?></span>

<?php
            if ($s["success"] !== FALSE):
?>

<center>
    <video width="640" height="480" controls>
        <source src="<?= $ms->makeUrl("pages/video.php?s=" . $mltc); ?>" type="<?= $s["type"]; ?>">
        <p>Sono spiacente, il tuo Browser non support l'embed video!</p>
    </video>
</center>

<?php
            else:
?>

<center>

<?php
                if (array_key_exists("error",$s) !== FALSE && ! empty($s["error"])):
?>

    <div class="alert alert-warning" role="alert">

<?php
                    switch($s["error"]):
                        case "media_not_available":
                            echo "Errore nella ricezione del Video richiesto!";
                            break;
                        case "media_not_found":
                            echo "Video non disponibile sul Server!";
                            break;
                    endswitch;
?>

    </div>

<?php
                endif;
?>

</center>

<?php
            endif;
        endif;
    endif;
?>
