@php
    if (!\Dcat\Admin\Admin::user()->isAdministrator()) {
        $projectList = \App\Models\ProjectModel::getProjectList(\Dcat\Admin\Admin::user()->id)->pluck('name', 'id')->toArray();
    } else {
        $projectList = \App\Models\ProjectModel::getAll()->pluck('name', 'id')->toArray();
    }
@endphp
<div class="btn-group">
    <button type="button" class="btn btn-success" id="project_title">{{ (\App\Admin\Controllers\AdminController::getProject())->name ?? '' }}</button>
    <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
        <span class="caret"></span>
    </button>
    <div class="dropdown-menu">
        <?php foreach ($projectList as $project_id => $project): ?>
            <a class="dropdown-item" onclick="Platform.update('{{ $project_id }}')">{{ $project }}</a>
        <?php endforeach; ?>
    </div>
</div>
<script type="text/javascript">
    var Platform = {
        update: function (project_id) {
            $.ajax({
                url: '/admin/change-project',
                type: 'POST',
                data: {
                    project_id: project_id
                },
                success: function(response) {
                    if (!response.status) {
                        Dcat.error(response.data.message);
                        return false;
                    }
                    Dcat.success(response.data.message);
                    $("#project_title").text(response.data.project);
                    var url = window.location.pathname;
                    if (url.indexOf('/admin/unit-test/api-detail/') !== -1) {
                        url = "/admin/unit-test/create";
                    }
                    Dcat.reload(url);
                },
                error: function(response) {
                    var errorData = JSON.parse(response.responseText);
                    if (errorData) {
                        Dcat.error(errorData.message);
                    }
                }
            });
        }
    }
</script>