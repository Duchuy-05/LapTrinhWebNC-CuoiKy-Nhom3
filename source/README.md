# StudyHub

StudyHub la website hoc tap truc tuyen duoc xay dung bang PHP Laravel theo mo hinh MVC, huong den viec quan ly de dang tren XAMPP va san sang mo rong trong tuong lai.

## Chuc nang da co

- Trang chu gioi thieu nen tang, thong bao, danh muc va khoa hoc noi bat.
- Dang ky, dang nhap, dang xuat cho hoc vien.
- Danh sach khoa hoc co tim kiem va loc theo danh muc.
- Trang chi tiet khoa hoc, danh sach bai hoc, hoc thu bai preview.
- Tham gia khoa hoc va theo doi tien do hoc tap.
- Dashboard hoc vien.
- Khu admin de quan ly danh muc, khoa hoc, bai hoc, nguoi dung, thong bao va noi dung tinh.
- Seeder du lieu mau kem tai khoan demo.

## Tai khoan demo

Sau khi chay seed, ban co the dang nhap bang:

- Admin: `admin@studyhub.local` / `password123`
- Instructor: `giangvien@studyhub.local` / `password123`
- Student: `hocvien@studyhub.local` / `password123`

## Chay nhanh bang Laravel built-in server

1. Mo terminal tai thu muc du an.
2. Neu can, chay `composer install`.
3. Tao file `.env` tu `.env.example` va chinh lai ket noi CSDL neu can.
4. Neu dung SQLite de test nhanh, tao file `database/database.sqlite` va dat `DB_CONNECTION=sqlite` trong `.env`.
5. Chay `php artisan key:generate`.
6. Chay `php artisan migrate:fresh --seed`.
7. Chay `php artisan serve`.
8. Mo dia chi `http://127.0.0.1:8000`.

## Chay voi XAMPP + MySQL

1. Bat `Apache` va `MySQL` trong XAMPP Control Panel.
2. Tao database moi ten `studyhub` trong phpMyAdmin.
3. Copy `.env.example` thanh `.env`.
4. Kiem tra cac bien trong `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=studyhub`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=`
5. Chay `php artisan key:generate`.
6. Chay `php artisan migrate:fresh --seed`.
7. Dat project trong `htdocs` hoac tao VirtualHost tro den thu muc `public` cua du an.
8. Neu dat trong `htdocs/studyhub`, truy cap `http://localhost/studyhub/public`.

## Cau truc chinh

- `app/Http/Controllers`: xu ly route public, auth va admin.
- `app/Models`: quan he du lieu cho user, category, course, lesson, enrollment, progress.
- `database/migrations`: schema co so du lieu.
- `database/seeders`: du lieu demo va noi dung mac dinh.
- `resources/views`: giao dien cho hoc vien va admin.
- `public/css/app.css`: stylesheet chung.

## Dinh huong mo rong

Kien truc hien tai da san sang de bo sung them:

- quiz / bai kiem tra truc tuyen
- thanh toan khoa hoc
- cap chung chi
- upload video, tai lieu that
- phan quyen sau hon cho giang vien
- toi uu hieu nang cho nhieu nguoi dung dong thoi
