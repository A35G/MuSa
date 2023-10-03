<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Gianluigi 'A35G'">
        <title>MuSa Project</title>
        <link href="https://getbootstrap.com/docs/5.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <!-- Font Awesome Library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <meta name="theme-color" content="#712cf9">
        <link rel="stylesheet" href="<?= \App\Core\Musa::makeUrl("assets/css/style.css"); ?>" />
    </head>
    <body>

        <header data-bs-theme="dark">
            <div class="navbar navbar-dark bg-dark shadow-sm">
                <div class="container">
                    <a href="<?= \App\Core\Musa::makeUrl(); ?>" class="navbar-brand d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true" class="me-2" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <strong>MuSa Project</strong>
                    </a>

                    <form id="meza" method="POST" class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control form-control-sm form-control-dark text-bg-dark" placeholder="Cerca..." aria-label="Cerca">
                            <span class="input-group-text border border-0 align-middle"><i class="fas fa-search"></i></span>
                        </div>
                    </form>

                </div>
            </div>
        </header>

        <main>

            <section class="py-3 text-center container">
                <div class="row">
                    <div class="col-lg-6 col-md-8 mx-auto">
                        <h1 class="fw-light">MuSa Project</h1>
                    </div>
                </div>
            </section>

            <div class="album py-5 bg-body-tertiary">
                <div class="container">

                    <nav class="mb-4" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="brdc" class="breadcrumb">
                            <li id="homeli" class="breadcrumb-item active" aria-current="page"><a href="<?= self::makeUrl(); ?>" class="text-reset text-decoration-none">Home</a></li>
                            <li id="ricerca" class="breadcrumb-item active d-none" aria-current="page">Hai cercato: <span id="qry"></span></li>
                            <li id="viewli" class="breadcrumb-item active d-none" aria-current="page"><span id="ttlv"></span></li>
                        </ol>
                    </nav>

                    <div id="boxd">
                        <?= $appContent; ?>
                    </div>

                </div>
            </div>

        </main>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <!-- Bootstrap -->
        <script src="https://getbootstrap.com/docs/5.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Internal -->
        <script src="<?= \App\Core\Musa::makeUrl("assets/js/core.js"); ?>"></script>
    </body>
</html>
