<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">

<?php
    if (isset($archive) && NULL !== $archive && ! empty($archive)):
        $tbx = 1;
        foreach ($archive as $smd):
?>

    <div class="col">
        <div class="card shadow-sm">

            <div class="card-body">
                <h5 class="card-title"><?= $smd["msTitle"]; ?></h5>
                <p class="card-text" style="height: 50px;"><em>&nbsp;</em></p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-body-secondary">&nbsp;</small>
                    <div class="btn-group">
                        <a href="<?= $smd["msUrlVideo"]; ?>" data-src="<?= $smd["msIdentity"]; ?>" class="btn btn-sm btn-outline-secondary">Avvia</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php
            ++$tbx;
        endforeach;
    endif;
?>

</div>
