<?php
    include('../inc/functions.php');

    // Mode édition si un dept_no valide est passé dans l'URL
    $dept_no_url = $_GET['dept_no'] ?? '';
    $editing = $dept_no_url !== '' && get_one_department($dept_no_url);

    $error   = '';
    $success = false;
    // Valeurs affichées dans le formulaire
    $dept_no   = $dept_no_url;
    $dept_name = $editing ? $editing['dept_name'] : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mode      = $_POST['mode'] ?? 'add';
        $dept_no   = trim($_POST['dept_no'] ?? '');
        $dept_name = trim($_POST['dept_name'] ?? '');

        if ($dept_no === '' || $dept_name === '') {
            $error = "Le numéro et le nom du département sont obligatoires.";
        } elseif (strlen($dept_no) > 4) {
            $error = "Le numéro de département fait au maximum 4 caractères.";
        } elseif ($mode === 'add' && get_one_department($dept_no)) {
            $error = "Un département avec le numéro '$dept_no' existe déjà.";
        } else {
            if ($mode === 'edit') {
                update_department($dept_no, $dept_name);
            } else {
                add_department($dept_no, $dept_name);
            }
            $success = true;
            $editing = true; // après ajout, on passe en mode édition
        }
    }
?>
<html>
    <head>
        <title><?= $editing ? "Modifier" : "Ajouter" ?> un département</title>
    </head>
    <body>
    <p><a href="index.php">&larr; Retour aux départements</a></p>
    <h1><?= $editing ? "Modifier le département $dept_no" : "Ajouter un département" ?></h1>

    <?php if ($success) { ?>
        <p style="color:green;">Enregistré.</p>
    <?php } ?>
    <?php if ($error !== '') { ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="post" action="dept_form.php<?= $editing ? '?dept_no=' . urlencode($dept_no) : '' ?>">
        <input type="hidden" name="mode" value="<?= $editing ? 'edit' : 'add' ?>">
        <p>
            Numéro (4 car. max) :
            <input type="text" name="dept_no" maxlength="4"
                   value="<?= htmlspecialchars($dept_no) ?>"
                   <?= $editing ? 'readonly' : '' ?>>
        </p>
        <p>Nom : <input type="text" name="dept_name" value="<?= htmlspecialchars($dept_name) ?>"></p>
        <p><input type="submit" value="<?= $editing ? 'Modifier' : 'Ajouter' ?>"></p>
    </form>
    </body>
</html>
