<?php
    include('../inc/functions.php');

    // $_GET['dept_no'] = valeur du paramètre passé dans l'URL (ex. employees.php?dept_no=d009).
    // ?? est l'opérateur de "coalescence des nuls" (null coalescing operator, PHP 7+).
    // Il signifie : "prends $_GET['dept_no'] s'il EXISTE et n'est PAS null, sinon prends ''".
    // Cela évite un warning "Undefined array key" si l'URL ne contient pas le paramètre.
    // Équivaut à : isset($_GET['dept_no']) ? $_GET['dept_no'] : ''
    $dept_no = $_GET['dept_no'] ?? '';
    $department = get_one_department($dept_no);

    // --- Pagination ---
    $par_page = 20;
    // Numéro de page courant (1 minimum), récupéré dans l'URL
    $page = max(1, (int)($_GET['page'] ?? 1));
    // OFFSET = nombre de lignes à sauter avant de commencer
    $offset = ($page - 1) * $par_page;

    $total = count_employees_by_department($dept_no);     // nombre total d'employés
    $nb_pages = (int)ceil($total / $par_page);            // nombre total de pages

    $employees = get_employees_by_department($dept_no, $par_page, $offset);
?>
<html>
    <head>
        <title>Employés du département</title>
    </head>
    <body>
    <p><a href="index.php">&larr; Retour aux départements</a></p>

    <?php if (!$department) { ?>
        <h1>Département introuvable</h1>
    <?php } else { ?>
        <h1>Employés du département <?= $department['dept_name'] ?> (<?= $department['dept_no'] ?>)</h1>
        <table border="1">
            <tr>
                <th>N°</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Genre</th>
                <th>Date d'embauche</th>
            </tr>
            <?php foreach ($employees as $emp) { ?>
                <tr>
                    <td><a href="fiche.php?emp_no=<?= urlencode($emp['emp_no']) ?>"><?= $emp['emp_no'] ?></a></td>
                    <td><?= $emp['first_name'] ?></td>
                    <td><?= $emp['last_name'] ?></td>
                    <td><?= $emp['gender'] ?></td>
                    <td><?= $emp['hire_date'] ?></td>
                </tr>
            <?php } ?>
        </table>
        <p>
            <?php if ($page > 1) { ?>
                <a href="employees.php?dept_no=<?= urlencode($dept_no) ?>&page=<?= $page - 1 ?>">&larr; Précédent</a>
            <?php } ?>

            Page <?= $page ?> / <?= $nb_pages ?>

            <?php if ($page < $nb_pages) { ?>
                <a href="employees.php?dept_no=<?= urlencode($dept_no) ?>&page=<?= $page + 1 ?>">Suivant &rarr;</a>
            <?php } ?>
        </p>
        <p><?= $total ?> employé(s) au total dans ce département.</p>
    <?php } ?>
    </body>
</html>
