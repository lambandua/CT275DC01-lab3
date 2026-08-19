<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

$id = isset($_REQUEST['id'])
  ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT)
  : false;

if (!$id || !$contact->find($id)) {
  redirect('/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $contactData = [
    'name' => $_POST['name'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'notes' => $_POST['notes'] ?? '',
    'avatar' => $contact->avatar,
  ];
  if (
    isset($_FILES['avatar'])
    && $_FILES['avatar']['error'] === UPLOAD_ERR_OK
  ) {
    $uploadDir = __DIR__ . '/uploads/';

    $extension = strtolower(
      pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION)
    );

    $fileName = uniqid('avatar_', true) . '.' . $extension;

    move_uploaded_file(
      $_FILES['avatar']['tmp_name'],
      $uploadDir . $fileName
    );

    $contactData['avatar'] = '/uploads/' . $fileName;
  }
  $errors = $contact->validate($contactData);

  if (empty($errors)) {
    $contact->fill($contactData);
    if ($contact->save()) {
      redirect('/');
    }
  }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>

  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'Update your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <form method="post" enctype="multipart/form-data" class="col-md-6 offset-md-3">

          <input
            type="hidden"
            name="id"
            value="<?= $contact->id ?>">

          <!-- Name -->
          <div class="mb-3">

            <label for="name" class="form-label">
              Name
            </label>

            <input
              type="text"
              name="name"
              class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
              maxlength="255"
              id="name"
              placeholder="Enter Name"
              value="<?= html_escape($contact->name) ?>" />

            <?php if (isset($errors['name'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['name'] ?></strong>
              </span>
            <?php endif ?>

          </div>

          <!-- Phone -->
          <div class="mb-3">

            <label for="phone" class="form-label">
              Phone Number
            </label>

            <input
              type="text"
              name="phone"
              class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
              maxlength="255"
              id="phone"
              placeholder="Enter Phone"
              value="<?= html_escape($contact->phone) ?>" />

            <?php if (isset($errors['phone'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['phone'] ?></strong>
              </span>
            <?php endif ?>

          </div>

          <!-- Notes -->
          <div class="mb-3">

            <label for="notes" class="form-label">
              Notes
            </label>

            <textarea
              name="notes"
              id="notes"
              class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"
              placeholder="Enter notes (maximum character limit: 255)"><?= html_escape($contact->notes) ?></textarea>

            <?php if (isset($errors['notes'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['notes'] ?></strong>
              </span>
            <?php endif ?>

          </div>
          <!-- Avatar -->
          <div class="mb-3">

            <label for="avatar" class="form-label">
              Avatar
            </label>

            <?php if (!empty($contact->avatar)): ?>
              <div class="mb-2">
                <img
                  src="<?= html_escape($contact->avatar) ?>"
                  alt="Avatar"
                  width="100"
                  height="100"
                  class="rounded-circle object-fit-cover">
              </div>
            <?php endif ?>

            <input
              type="file"
              name="avatar"
              id="avatar"
              class="form-control"
              accept="image/*">

          </div>
          <!-- Submit -->
          <button
            type="submit"
            name="submit"
            class="btn btn-primary">
            Update Contact
          </button>

        </form>

      </div>
    </div>

  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

</body>

</html>