<?php
    include('../inc/functions.php');

    $emp_no_url = $_GET['emp_no'] ?? '';
    $existing   = $emp_no_url !== '' ? get_one_employee($emp_no_url) : null;
    $editing    = (bool)$existing;

    $departments = get_all_departments();

    $error   = '';
    $success = false;

    // --- Valeurs du formulaire (pré-remplies en édition) ---
    $emp_no     = $emp_no_url;
    $first_name = $existing['first_name'] ?? '';
    $last_name  = $existing['last_name']  ?? '';
    $gender     = $existing['gender']     ?? 'M';
    $birth_date = $existing['birth_date'] ?? '';
    $hire_date  = $existing['hire_date']  ?? '';
    $dept_no    = $existing['dept_no']    ?? '';

    // Est-il déjà le manager de son département actuel ?
    $mgr = $dept_no ? get_current_manager($dept_no) : null;
    $is_manager = $mgr && $mgr['emp_no'] == $emp_no;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mode       = $_POST['mode'] ?? 'add';
        $emp_no     = trim($_POST['emp_no'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $gender     = $_POST['gender'] ?? 'M';
        $birth_date = $_POST['birth_date'] ?? '';
        $hire_date  = $_POST['hire_date'] ?? '';
        $dept_no    = $_POST['dept_no'] ?? '';
        $is_manager = isset($_POST['is_manager']);   // la case n'est envoyée que si cochée

        // Validation
        if ($emp_no === '' || $first_name === '' || $last_name === ''
            || $birth_date === '' || $hire_date === '' || $dept_no === '') {
            $error = "Tous les champs sont obligatoires (sauf la case manager).";
        } elseif ($mode === 'add' && get_one_employee($emp_no)) {
            $error = "Un employé avec le numéro '$emp_no' existe déjà.";
        } else {
            $today = date('Y-m-d');

            if ($mode === 'edit') {
                update_employee($emp_no, $birth_date, $first_name, $last_name, $gender, $hire_date);
                // Département : on ne change que s'il a été modifié (date d'effet = aujourd'hui)
                $current = get_current_department($emp_no);
                if (!$current || $current['dept_no'] !== $dept_no) {
                    change_department($emp_no, $dept_no, $today);
                }
            } else {
                add_employee($emp_no, $birth_date, $first_name, $last_name, $gender, $hire_date);
                // Nouveau salarié : on l'affecte à son département (date d'effet = date d'embauche)
                change_department($emp_no, $dept_no, $hire_date);
            }

            // Gestion du statut manager sur le département choisi
            $mgr = get_current_manager($dept_no);
            $is_now = $mgr && $mgr['emp_no'] == $emp_no;
            if ($is_manager && !$is_now) {
                make_manager($emp_no, $dept_no, $mode === 'add' ? $hire_date : $today);
            } elseif (!$is_manager && $is_now) {
                remove_manager($dept_no, $today);
            }

            $success = true;
            $editing = true;
        }
    }
?>
<html>
    <head>
        <title><?= $editing ? "Modifier" : "Ajouter" ?> un employé</title>
    </head>
    <body>
    <p><a href="index.php">&larr; Retour aux départements</a></p>
    <h1><?= $editing ? "Modifier l'employé $emp_no" : "Ajouter un employé" ?></h1>

    <?php if ($success) { ?>
        <p style="color:green;">Enregistré.
           <a href="fiche.php?emp_no=<?= urlencode($emp_no) ?>">Voir la fiche &rarr;</a></p>
    <?php } ?>
    <?php if ($error !== '') { ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="post" action="emp_form.php<?= $editing ? '?emp_no=' . urlencode($emp_no) : '' ?>">
        <input type="hidden" name="mode" value="<?= $editing ? 'edit' : 'add' ?>">
        <p>Numéro : <input type="number" name="emp_no" value="<?= htmlspecialchars($emp_no) ?>" <?= $editing ? 'readonly' : '' ?>></p>
        <p>Prénom : <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>"></p>
        <p>Nom : <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>"></p>
        <p>Genre :
            <select name="gender">
                <option value="M" <?= $gender === 'M' ? 'selected' : '' ?>>M</option>
                <option value="F" <?= $gender === 'F' ? 'selected' : '' ?>>F</option>
            </select>
        </p>
        <p>Date de naissance : <input type="date" name="birth_date" value="<?= htmlspecialchars($birth_date) ?>"></p>
        <p>Date d'embauche : <input type="date" name="hire_date" value="<?= htmlspecialchars($hire_date) ?>"></p>
        <p>Département :
            <select name="dept_no">
                <option value="">— Choisir —</option>
                <?php foreach ($departments as $d) { ?>
                    <option value="<?= $d['dept_no'] ?>" <?= $dept_no === $d['dept_no'] ? 'selected' : '' ?>>
                        <?= $d['dept_name'] ?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label>
                <input type="checkbox" name="is_manager" value="1" <?= $is_manager ? 'checked' : '' ?>>
                Est manager de ce département
            </label>
        </p>
        <p><input type="submit" value="<?= $editing ? 'Modifier' : 'Ajouter' ?>"></p>
    </form>
    </body>
</html>
