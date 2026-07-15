# Trien khai CB Company tren aaPanel

Tai lieu nay dung cho website production da co WordPress va domain. Khong can tao
WordPress moi, khong import SQL thu cong va khong thay `wp-config.php`.

## 1. Sao luu truoc khi trien khai

Trong aaPanel, tao backup cho ca hai thanh phan:

1. Website files cua domain.
2. Database WordPress.

Khong tiep tuc neu backup chua hoan tat hoac khong tai duoc file backup.

## 2. Kiem tra PHP

Bat cac extension sau cho dung phien ban PHP ma website dang su dung:

- `zip`
- `fileinfo`
- `gd` hoac `imagick`
- `mbstring`
- `json`

Dat `upload_max_filesize` va `post_max_size` lon hon package `.cbsite.zip`. Neu
dung Nginx, `client_max_body_size` cung phai lon hon package.

Thu muc `wp-content/uploads` phai ghi duoc boi user chay PHP. Quyen thong thuong:

- Directory: `755`
- File: `644`

## 3. Build bo cai tren may local

Tai thu muc repo, chay PowerShell:

```powershell
./scripts/build-wordpress-packages.ps1
```

Ket qua nam trong `dist/`:

- `cb-company-core.zip`
- `cb-webp-converter.zip`
- `cb-site-transfer.zip`
- `cb-company-theme.zip`

Moi ZIP phai co dung mot thu muc goc cung ten package. Vi du file khoi tao cua
Site Transfer phai co duong dan:

```text
cb-site-transfer/cb-site-transfer.php
```

Khong duoc la:

```text
cb-site-transfer/cb-site-transfer/cb-site-transfer.php
```

## 4. Cai code len production

Trong WordPress Admin, vao **Plugins > Add Plugin > Upload Plugin** va cai theo
thu tu:

1. `cb-company-core.zip`
2. `cb-webp-converter.zip`
3. `cb-site-transfer.zip`

Sau do vao **Appearance > Themes > Add New > Upload Theme**, cai va kich hoat
`cb-company-theme.zip` neu production chua dung theme nay.

Khong giai nen ZIP vao mot thu muc cung ten da tao san. WordPress se tu tao thu
muc package. Neu WordPress bao `Destination folder already exists`, xoa thu muc
cai loi cu trong aaPanel truoc khi thu lai.

## 5. Sua loi "Tap tin cua plugin khong ton tai"

Loi nay xay ra voi ZIP cu tao tren Windows, khi ten ZIP entry dung dau `\`.

1. Trong aaPanel, mo **Files**.
2. Di toi `/www/wwwroot/TEN-DOMAIN/wp-content/plugins/`.
3. Xoa rieng thu muc cai loi `cb-site-transfer`.
4. Neu aaPanel hien cac file co ten bat dau bang `cb-site-transfer\`, xoa rieng
   cac file do.
5. Khong xoa bat ky plugin khac.
6. Quay lai WordPress va upload lai `dist/cb-site-transfer.zip` moi.
7. Kich hoat plugin va kiem tra menu **CB Company > Nhap / Xuat website**.

Duong dan dung tren server sau khi cai:

```text
/www/wwwroot/TEN-DOMAIN/wp-content/plugins/cb-site-transfer/cb-site-transfer.php
```

Neu khong the upload trong WordPress, co the upload ZIP bang aaPanel va giai nen
truc tiep tai `wp-content/plugins/`. Sau khi giai nen, van phai dam bao chi co mot
cap thu muc `cb-site-transfer`.

## 6. Export du lieu tren local

1. Mo **CB Company > Nhap / Xuat website > Export**.
2. Giu tat **Inquiry** tru khi thuc su can chuyen du lieu ca nhan.
3. Bam **Tao goi trien khai**.
4. Tai file `company-site.cbsite.zip`.
5. Giu lai checksum SHA-256 va mot ban package goc.

## 7. Import vao production

1. Mo **CB Company > Nhap / Xuat website > Import**.
2. Upload `company-site.cbsite.zip`.
3. Kiem tra Source URL, Target URL, version, dung luong, conflict va dependency.
4. Chon **Tao moi va cap nhat** cho lan trien khai thong thuong.
5. Chay **Dry run** truoc.
6. Chi bam **Import** khi dry run khong co blocking error.
7. Khong dong tab cho den khi job bao hoan tat. Neu mat ket noi, dung **Tiep tuc**.

Plugin khong thay domain production, user admin, database credentials, salts hay
`wp-config.php`.

## 8. Smoke test sau import

Kiem tra toi thieu:

- `/en/` va `/zh/`
- Home EN va Home ZH
- Hero desktop/mobile
- Page Builder va cac anh trong section
- Product archive, product detail, gallery va featured image
- Menu desktop/mobile
- About, Contact va special pages
- Translation links EN/ZH
- Form inquiry va email neu production da cau hinh SMTP

Vao **Settings > Permalinks** va bam **Save Changes** neu route cu chua duoc lam
moi.

## 9. Rollback

Neu import tao ket qua sai:

1. Mo tab **Rollback**.
2. Chon snapshot cua import job vua chay.
3. Xac nhan hai buoc.
4. Kiem tra lai EN/ZH va menu.

Plugin chi giu toi da ba snapshot gan nhat. Backup aaPanel van la lop khoi phuc
cuoi cung va khong nen xoa ngay sau khi trien khai.
