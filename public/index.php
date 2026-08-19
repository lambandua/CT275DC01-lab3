<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contact = new Contact($PDO);

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']))
  ? (int) $_GET['limit']
  : 5;

$page = (isset($_GET['page']) && is_numeric($_GET['page']))
  ? (int) $_GET['page']
  : 1;

$paginator = new Paginator(
  totalRecords: $contact->count(),
  recordsPerPage: $limit,
  currentPage: $page
);

$contacts = $contact->paginate(
  $paginator->recordOffset,
  $paginator->recordsPerPage
);

$pages = $paginator->getPages(length: 3);

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>

  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'View all your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <a href="/add.php" class="btn btn-primary mb-3">
          <i class="fa fa-plus"></i> New Contact
        </a>

        <!-- Table Starts Here -->
        <table id="contacts" class="table table-striped table-bordered">

          <thead>
            <tr>
              <th scope="col">Avatar</th>
              <th scope="col">Name</th>
              <th scope="col">Phone</th>
              <th scope="col">Date Created</th>
              <th scope="col">Notes</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>

          <tbody>

            <?php foreach ($contacts as $contact): ?>
              <tr>
                <td>
                  <?php if (!empty($contact->avatar)): ?>
                    <img
                      src="<?= html_escape($contact->avatar) ?>"
                      alt="Avatar"
                      width="60"
                      height="60"
                      class="rounded-circle object-fit-cover">
                  <?php else: ?>
                    No avatar
                  <?php endif ?>
                </td>
                <td>
                  <?= htmlspecialchars($contact->name) ?>
                </td>
                <td>
                  <?= htmlspecialchars($contact->phone) ?>
                </td>
                <td>
                  <?= htmlspecialchars($contact->created_at) ?>
                </td>
                <td>
                  <?= htmlspecialchars($contact->notes) ?>
                </td>
                <td>
                  <a
                    href="/edit.php?id=<?= $contact->id ?>"
                    class="btn btn-sm btn-primary">
                    <i class="fa fa-edit"></i> Edit
                  </a>
                  <form
                    class="d-inline ms-1"
                    action="/delete.php"
                    method="POST">
                    <input
                      type="hidden"
                      name="id"
                      value="<?= $contact->id ?>">
                    <button
                      type="submit"
                      class="btn btn-sm btn-danger"
                      name="delete-contact">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <!-- Table Ends Here -->

        <!-- Pagination -->
        <nav class="d-flex justify-content-center">
          <ul class="pagination">

            <!-- Previous -->
            <li class="page-item<?= $paginator->getPrevPage() ? '' : ' disabled' ?>">
              <a
                role="button"
                href="/?page=<?= $paginator->getPrevPage() ?>&limit=<?= $limit ?>"
                class="page-link">
                <span>&laquo;</span>
              </a>
            </li>

            <!-- Page Numbers -->
            <?php foreach ($pages as $page): ?>
              <li class="page-item<?= $paginator->currentPage == $page ? ' active' : '' ?>">
                <a
                  role="button"
                  href="/?page=<?= $page ?>&limit=<?= $limit ?>"
                  class="page-link">
                  <?= $page ?>
                </a>
              </li>
            <?php endforeach; ?>

            <!-- Next -->
            <li class="page-item<?= $paginator->getNextPage() ? '' : ' disabled' ?>">
              <a
                role="button"
                href="/?page=<?= $paginator->getNextPage() ?>&limit=<?= $limit ?>"
                class="page-link">
                <span>&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- Pagination Ends Here -->
      </div>
    </div>
  </div>


  <!-- Delete Confirmation Modal -->
  <div id="delete-confirm" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation</h4>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal">
          </button>
        </div>
        <div class="modal-body">
          Do you want to delete this contact?
        </div>
        <div class="modal-footer">
          <button
            type="button"
            data-bs-dismiss="modal"
            class="btn btn-danger"
            id="delete">
            Delete
          </button>
          <button
            type="button"
            data-bs-dismiss="modal"
            class="btn btn-default">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>
  <script>
    const deleteButtons = document.querySelectorAll(
      'button[name="delete-contact"]'
    );

    deleteButtons.forEach(button => {

      button.addEventListener('click', function(e) {

        e.preventDefault();

        const form = button.closest('form');

        const nameTd = button
          .closest('tr')
          .querySelector('td:nth-child(2)');

        if (nameTd) {
          document.querySelector('.modal-body').textContent =
            `Do you want to delete "${nameTd.textContent}"?`;
        }

        const submitForm = function() {
          form.submit();
        };

        document
          .getElementById('delete')
          .addEventListener('click', submitForm, {
            once: true
          });

        const modalEl = document.getElementById('delete-confirm');

        modalEl.addEventListener(
          'hidden.bs.modal',
          function() {
            document
              .getElementById('delete')
              .removeEventListener('click', submitForm);
          }
        );

        const confirmModal = new bootstrap.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false
        });

        confirmModal.show();
      });

    });
  </script>
</body>

</html>