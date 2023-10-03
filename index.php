<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include __DIR__ . "/libs/Core.php";

$data = array(
    "appContent"    =>  ""
);

$loadFullTemplate = TRUE;

if (isset($_GET["mdtk"]) && NULL !== $_GET["mdtk"]):

	$mltc = filter_input(INPUT_GET, 'mdtk', FILTER_SANITIZE_ENCODED);
	if (NULL !== $mltc && $mltc !== FALSE):

		ob_start();
		include __DIR__ . "/pages/valors.php";
		$data["appContent"] = ob_get_clean();

	endif;

elseif (isset($_POST["usaf"]) && NULL !== $_POST["usaf"]):

    $strq = rawurldecode($_POST["usaf"]);
    $qrst = filter_var($strq, FILTER_SANITIZE_STRING);

    if (NULL !== $qrst && $qrst !== FALSE):

        $mdf = $ms->searchMediaByForm($qrst);
        if (isset($mdf) && ! empty($mdf) && $ms->checkIsValidJSON($mdf)):

            $dtrsc = json_decode($mdf,TRUE);
            if (isset($dtrsc) && is_array($dtrsc) && array_key_exists("archive", $dtrsc) !== FALSE):

            	$loadFullTemplate = FALSE;

                ob_start();

                extract($dtrsc);
                include __DIR__ . "/view/mediaBlock.php";
                $dataContent = ob_get_contents();
                ob_end_clean();

                echo $dataContent;
            endif;
        endif;
    endif;

else:
	$data["appContent"] = $ms->loadArchiveVideo();
endif;

if ($loadFullTemplate !== FALSE):
	$ms->makeView("view/archive",$data);
endif;
