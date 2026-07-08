<?php
    include('../inc/functions.php');

    $emp_no   = $_GET['emp_no'] ?? '';
    $employee = get_one_employee($emp_no);
    $current  = get_current_department($emp_no);

    $error   = '';
    $success = false;

    // Traitement du formulaire (méthode POST car on modifie la base)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_dept = $_POST['dept_no']   ?? '';
        $start    = $_POST['from_date'] ?? '';

        if ($new_dept === '' || $start === '') {
            $error = "Veuillez choisir un département et une date de début.";
        } elseif ($current && $start < $current['from_date']) {
            // c. Erreur si la date de début est antérieure à celle du département actuel
            $error = "La date de début ($start) ne peut pas être antérieure à celle du département actuel (" . $current['from_date'] . ").";
        } else {
            change_department($emp_no, $new_dept, $start);
            $success = true;
            // a. On recharge le département courant pour vérifier qu'il a bien changé
            $current = get_current_department($emp_no);
        }
    }

    // b. La liste déroulante exclut le département actuel
    $departments = get_departments_except($current ? $current['dept_no'] : '');
?>
<html>
    <head>
        <title>Changer de département</title>
    </head>
    <body>
    <p><a href="fiche.php?emp_no=<?= urlencode($emp_no) ?>">&larr; Retour à la fiche</a></p>

    <?php if (!$employee) { ?>
        <h1>Employé introuvable</h1>
    <?php } else { ?>
        <h1>Changer le département de <?= $employee['first_name'] ?> <?= $employee['last_name'] ?></h1>

        <?php if ($success) { ?>
            <p style="color:green;">Changement effectué.</p>
        <?php } ?>
        <?php if ($error !== '') { ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php } ?>

        <!-- b. Département actuel affiché en haut, avec sa date de début -->
        <p>
            <strong>Département actuel :</strong>
            <?= $current ? $current['dept_name'] . ' (depuis le ' . $current['from_date'] . ')' : 'aucun' ?>
        </p>

        <form method="post" action="change_dept.php?emp_no=<?= urlencode($emp_no) ?>">
            <p>
                Nouveau département :
                <select name="dept_no">
                    <option value="">— Choisir —</option>
                    <?php foreach ($departments as $d) { ?>
                        <option value="<?= $d['dept_no'] ?>"><?= $d['dept_name'] ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>Date de début : <input type="date" name="from_date"></p>
            <p><input type="submit" value="Changer de département"></p>
        </form>
    <?php } ?>
    </body>
</html>
