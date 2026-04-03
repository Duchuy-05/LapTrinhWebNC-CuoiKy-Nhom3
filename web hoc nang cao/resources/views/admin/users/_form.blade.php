<div class="form-grid">
    <div class="form-field">
        <label for="name">Họ tên</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="role">Vai trò</label>
        <select id="role" name="role" required>
            @foreach (['admin' => 'Quản trị viên', 'instructor' => 'Giảng viên', 'student' => 'Học viên'] as $roleValue => $roleLabel)
                <option value="{{ $roleValue }}" @selected(old('role', $user->role ?? 'student') === $roleValue)>{{ $roleLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="password">Mật khẩu {{ isset($user) ? '(để trống nếu không đổi)' : '' }}</label>
        <input id="password" name="password" type="password" {{ isset($user) ? '' : 'required' }}>
    </div>
    <div class="form-field">
        <label for="password_confirmation">Xác nhận mật khẩu</label>
        <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($user) ? '' : 'required' }}>
    </div>
    <div class="form-field form-field-full">
        <label for="bio">Giới thiệu ngắn</label>
        <textarea id="bio" name="bio" rows="4">{{ old('bio', $user->bio ?? '') }}</textarea>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
        <span>Kích hoạt tài khoản</span>
    </label>
    <div class="surface-subcard note-card">
        <strong>Lưu ý:</strong>
        <p>Tài khoản giảng viên chỉ được tạo tại khu vực quản trị này. Người dùng bên ngoài chỉ có thể tự đăng ký dưới vai trò học viên.</p>
    </div>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.users.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu tài khoản</button>
</div>