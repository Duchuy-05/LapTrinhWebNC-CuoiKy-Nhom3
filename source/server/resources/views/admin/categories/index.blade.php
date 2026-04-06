@extends('layouts.admin')
@section('title', 'Quản lý Danh mục')
@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->name }}</td>
                    
                    <td>{{ $cat->slug }}</td>
                    
                    <td>
                        @if($cat->status == 1)
                            <span class="badge badge-success">Đang hiện</span>
                        @else
                            <span class="badge badge-secondary">Đang ẩn</span>
                        @endif
                    </td>
                    
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('admin.categories.edit', $cat->id) }}">
                                    <i class="fas fa-edit text-primary mr-2"></i> Sửa
                                </a>
                                
                                <div class="dropdown-divider"></div>
                                
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-trash text-danger mr-2"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection