@extends('layouts.admin')

@section('title', 'Quản lý Người Dùng')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mt-1">Danh sách User</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>
            </div>
            
            <div class="card-body table-responsive p-0">
                <div class="d-flex justify-content-end align-items-center p-3 pb-2">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm nhanh...">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                    </div>
                </div>
                <table class="table table-hover text-nowrap table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                        @csrf
                                        @method('DELETE') 
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if($users->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">Chưa có người dùng nào.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if(text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection