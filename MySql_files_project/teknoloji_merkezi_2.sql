-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- ÖĞRENCİLER TABLOSU

CREATE DATABASE IF NOT EXISTS teknoloji_merkezi;
USE teknoloji_merkezi;

-- Öğrenciler tablosu
CREATE TABLE ogrenciler (
    ogrenci_id INT PRIMARY KEY AUTO_INCREMENT,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    baba_adi VARCHAR(50),
    dogum_tarihi DATE,
    yas INT,
    cinsiyet ENUM('Erkek', 'Kız') NOT NULL,
    telefon VARCHAR(20),
    adres TEXT,
    sehir VARCHAR(50) DEFAULT 'Herat',
    ilce VARCHAR(50),
    veli_adi VARCHAR(100),
    veli_telefon VARCHAR(20),
    egitim_seviyesi ENUM('İlkokul', 'Ortaokul', 'Lise', 'Mezun') DEFAULT 'İlkokul',
    teknoloji_seviyesi ENUM('Hiç Bilmiyor', 'Temel', 'Orta', 'İleri') DEFAULT 'Hiç Bilmiyor',
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    durum ENUM('Aktif', 'Pasif', 'Mezun', 'Ayrılmış') DEFAULT 'Aktif',
    notlar TEXT,
    olusturan_admin VARCHAR(50),
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Örnek öğrenci verileri ekleme
INSERT INTO ogrenciler (ad, soyad, baba_adi, dogum_tarihi, yas, cinsiyet, telefon, adres, sehir, ilce, veli_adi, veli_telefon, egitim_seviyesi, teknoloji_seviyesi, notlar, olusturan_admin) VALUES
('Ahmad', 'Ahmadi', 'Mohammad', '2008-03-15', 16, 'Erkek', '+93701234567', 'Şehir Naw Mahallesi', 'Kabil', 'Şehir Naw', 'Mohammad Ahmadi', '+93701234560', 'Lise', 'Temel', 'Bilgisayara çok ilgili, matematik yeteneği var', 'admin'),

('Fatima', 'Karimi', 'Ali', '2009-07-22', 15, 'Kız', '+93702345678', 'Karte Char Mahallesi', 'Kabil', 'Karte Char', 'Maryam Karimi', '+93702345670', 'Ortaokul', 'Hiç Bilmiyor', 'Çok çalışkan, öğrenmeye istekli', 'admin'),

('Hassan', 'Nazari', 'Abdul Rahman', '2007-11-10', 17, 'Erkek', '+93703456789', 'Mikrorayon 3', 'Kabil', 'Mikrorayon', 'Abdul Rahman Nazari', '+93703456780', 'Lise', 'Orta', 'Daha önce basit programlama deneyimi var', 'admin'),

('Zainab', 'Hosseini', 'Ahmad Shah', '2010-01-08', 14, 'Kız', '+93704567890', 'Karte Se Mahallesi', 'Kabil', 'Karte Se', 'Jamila Hosseini', '+93704567800', 'Ortaokul', 'Temel', 'Robotik konularına çok meraklı', 'admin'),

('Omar', 'Wardak', 'Mohammad Ismail', '2006-12-03', 18, 'Erkek', '+93705678901', 'Darul Aman Yolu', 'Kabil', 'Darul Aman', 'Mohammad Ismail Wardak', '+93705678900', 'Mezun', 'İleri', 'Lise mezunu, üniversite hazırlığı yapıyor', 'admin');

-- Öğrenci bilgilerini görüntülemek için temel sorgular
-- Tüm aktif öğrencileri listele
SELECT ogrenci_id, CONCAT(ad, ' ', soyad) as tam_ad, yas, cinsiyet, sehir, egitim_seviyesi, teknoloji_seviyesi, kayit_tarihi 
FROM ogrenciler 
WHERE durum = 'Aktif' 
ORDER BY kayit_tarihi DESC;

-- Yaşa göre gruplandırma
SELECT 
    CASE 
        WHEN yas BETWEEN 12 AND 14 THEN '12-14 Yaş'
        WHEN yas BETWEEN 15 AND 17 THEN '15-17 Yaş'
        WHEN yas >= 18 THEN '18+ Yaş'
    END as yas_grubu,
    COUNT(*) as ogrenci_sayisi
FROM ogrenciler 
WHERE durum = 'Aktif'
GROUP BY yas_grubu;

-- Teknoloji seviyesine göre dağılım
SELECT teknoloji_seviyesi, COUNT(*) as sayi, 
       ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM ogrenciler WHERE durum = 'Aktif')), 2) as yuzde
FROM ogrenciler 
WHERE durum = 'Aktif'
GROUP BY teknoloji_seviyesi;




-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- EĞİTMENLER TABLOSU

USE teknoloji_merkezi;

-- Eğitmenler tablosu
CREATE TABLE egitmenler (
    egitmen_id INT PRIMARY KEY AUTO_INCREMENT,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    baba_adi VARCHAR(50),
    dogum_tarihi DATE,
    yas INT,
    cinsiyet ENUM('Erkek', 'Kadın') NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE,
    adres TEXT,
    
    -- Eğitim Bilgileri
    mezun_universite VARCHAR(100),
    fakulte VARCHAR(100),
    bolum VARCHAR(100),
    mezuniyet_yili YEAR,
    lisans_notu DECIMAL(3,2), -- 0.00-4.00 arası
    yuksek_lisans ENUM('Var', 'Yok', 'Devam Ediyor') DEFAULT 'Yok',
    yuksek_lisans_bolum VARCHAR(100),
    
    -- Uzmanlık Alanları
    uzmanlik_alani_1 ENUM('Yazılım Geliştirme', 'Web Tasarım', 'Robotik', 'Elektronik', 'Veri Analizi', 'Siber Güvenlik', 'Mobil Uygulama', 'Oyun Geliştirme') NOT NULL,
    uzmanlik_alani_2 ENUM('Yazılım Geliştirme', 'Web Tasarım', 'Robotik', 'Elektronik', 'Veri Analizi', 'Siber Güvenlik', 'Mobil Uygulama', 'Oyun Geliştirme'),
    programlama_dilleri TEXT, -- Python, Java, C++ vs.
    sertifikalar TEXT,
    
    -- Deneyim Bilgileri
    is_deneyimi_yil INT DEFAULT 0,
    onceki_is_yerleri TEXT,
    egitmenlik_deneyimi ENUM('Hiç Yok', '0-1 Yıl', '1-3 Yıl', '3+ Yıl') DEFAULT 'Hiç Yok',
    
    -- Merkez Bilgileri
    baslama_tarihi DATE NOT NULL,
    maas DECIMAL(8,2),
    pozisyon ENUM('Gönüllü', 'Yarı Zamanlı', 'Tam Zamanlı') DEFAULT 'Gönüllü',
    durum ENUM('Aktif', 'İzinli', 'Ayrılmış') DEFAULT 'Aktif',
    
    -- Kişisel Özellikler
    dil_bilgisi VARCHAR(200) DEFAULT 'Farsça, Türkçe, İngilizce',
    ozel_yetenekler TEXT,
    motivasyon_notu TEXT,
    
    -- Sistem Bilgileri
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    son_giris_tarihi DATETIME,
    olusturan_admin VARCHAR(50),
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Örnek eğitmen verileri ekleme
INSERT INTO egitmenler (
    ad, soyad, baba_adi, dogum_tarihi, yas, cinsiyet, telefon, email, adres,
    mezun_universite, fakulte, bolum, mezuniyet_yili, lisans_notu, yuksek_lisans, yuksek_lisans_bolum,
    uzmanlik_alani_1, uzmanlik_alani_2, programlama_dilleri, sertifikalar,
    is_deneyimi_yil, onceki_is_yerleri, egitmenlik_deneyimi,
    baslama_tarihi, maas, pozisyon, durum,
    dil_bilgisi, ozel_yetenekler, motivasyon_notu, olusturan_admin
) VALUES 
(
    'Mohammad Reza', 'Ahmadi', 'Ahmad Shah', '1995-06-15', 29, 'Erkek', '+93701111111', 'reza.ahmadi@gmail.com', 'Şehir Naw, Kabil',
    'İstanbul Teknik Üniversitesi', 'Bilgisayar ve Bilişim Fakültesi', 'Bilgisayar Mühendisliği', 2018, 3.45, 'Var', 'Yazılım Mühendisliği',
    'Yazılım Geliştirme', 'Web Tasarım', 'Python, JavaScript, PHP, MySQL, React', 'AWS Cloud Practitioner, Google Analytics',
    4, 'İstanbul - Yazılım Şirketi, Ankara - Freelance', '1-3 Yıl',
    '2024-01-15', 800.00, 'Tam Zamanlı', 'Aktif',
    'Farsça (Ana dil), Türkçe (İleri), İngilizce (Orta), Arapça (Temel)', 'Proje yönetimi, Takım liderliği', 'Memleketimin teknolojik kalkınmasına katkıda bulunmak istiyorum', 'admin'
),
(
    'Fatima', 'Karimi', 'Ali Mohammad', '1997-09-22', 27, 'Kadın', '+93702222222', 'fatima.karimi@outlook.com', 'Karte Char, Kabil',
    'Hacettepe Üniversitesi', 'Mühendislik Fakültesi', 'Elektrik-Elektronik Mühendisliği', 2020, 3.78, 'Devam Ediyor', 'Robotik Mühendisliği',
    'Robotik', 'Elektronik', 'C++, Arduino, Python, MATLAB', 'TÜBİTAK Robotik Yarışması 2. Lik',
    2, 'Ankara - Robotik Şirketi', 'Hiç Yok',
    '2024-02-01', 600.00, 'Yarı Zamanlı', 'Aktif',
    'Farsça (Ana dil), Türkçe (İleri), İngilizce (İyi)', 'Elektronik devre tasarımı, 3D modelleme', 'Kız çocuklarına STEM eğitimi vermek benim en büyük hayalim', 'admin'
),
(
    'Hassan', 'Nazari', 'Abdul Karim', '1993-03-10', 31, 'Erkek', '+93703333333', 'hassan.nazari@yahoo.com', 'Mikrorayon 4, Kabil',
    'Boğaziçi Üniversitesi', 'Mühendislik Fakültesi', 'Bilgisayar Mühendisliği', 2016, 3.92, 'Var', 'Siber Güvenlik',
    'Siber Güvenlik', 'Veri Analizi', 'Python, SQL, Linux, Kali Linux, Wireshark', 'CEH (Certified Ethical Hacker), CISSP',
    6, 'İstanbul - Siber Güvenlik Firması, Dubai - Danışmanlık', '3+ Yıl',
    '2023-12-01', 1000.00, 'Tam Zamanlı', 'Aktif',
    'Farsça (Ana dil), Türkçe (İleri), İngilizce (İleri), Arapça (Orta)', 'Penetrasyon testleri, Network güvenliği', 'Ülkemizin dijital güvenliğini sağlamak için geldim', 'admin'
);

-- Eğitmen bilgilerini görüntülemek için temel sorgular

-- Tüm aktif eğitmenleri uzmanlık alanlarıyla listele
SELECT 
    egitmen_id,
    CONCAT(ad, ' ', soyad) as tam_ad,
    yas,
    mezun_universite,
    bolum,
    uzmanlik_alani_1,
    uzmanlik_alani_2,
    pozisyon,
    baslama_tarihi
FROM egitmenler 
WHERE durum = 'Aktif' 
ORDER BY baslama_tarihi;

-- Uzmanlık alanlarına göre eğitmen dağılımı
SELECT uzmanlik_alani_1 as uzmanlik_alani, COUNT(*) as egitmen_sayisi
FROM egitmenler 
WHERE durum = 'Aktif'
GROUP BY uzmanlik_alani_1
UNION
SELECT uzmanlik_alani_2 as uzmanlik_alani, COUNT(*) as egitmen_sayisi
FROM egitmenler 
WHERE durum = 'Aktif' AND uzmanlik_alani_2 IS NOT NULL
GROUP BY uzmanlik_alani_2
ORDER BY egitmen_sayisi DESC;

-- Pozisyona göre maaş ortalaması
SELECT 
    pozisyon,
    COUNT(*) as egitmen_sayisi,
    AVG(maas) as ortalama_maas,
    MIN(maas) as min_maas,
    MAX(maas) as max_maas
FROM egitmenler 
WHERE durum = 'Aktif' AND maas > 0
GROUP BY pozisyon;




-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- KURSLAR TABLOSU
USE teknoloji_merkezi;

-- Kurslar tablosu
CREATE TABLE kurslar (
    kurs_id INT PRIMARY KEY AUTO_INCREMENT,
    kurs_adi VARCHAR(100) NOT NULL,
    kurs_kodu VARCHAR(20) UNIQUE NOT NULL,
    aciklama TEXT,
    kategori ENUM('Temel Bilgisayar', 'Yazılım Geliştirme', 'Yapay Zeka', 'Siber Güvenlik') NOT NULL,
    
    -- Seviye ve Süre Bilgileri
    seviye ENUM('Başlangıç', 'Orta', 'İleri') NOT NULL,
    yas_grubu VARCHAR(50), -- örn: "12-15 yaş", "16+ yaş"
    toplam_ders_saati INT NOT NULL,
    haftalik_ders_saati INT NOT NULL,
    kurs_suresi_hafta INT NOT NULL,
    
    -- Ön Koşullar ve Hedefler
    on_kosul TEXT,
    hedef_beceriler TEXT,
    kullanilacak_araclar TEXT,
    
    -- Eğitmen ve Kapasite
    sorumlu_egitmen_id INT,
    yardimci_egitmen_id INT,
    maksimum_ogrenci INT DEFAULT 12,
    minimum_ogrenci INT DEFAULT 5,
    
    -- Tarih ve Durum Bilgileri
    baslama_tarihi DATE,
    bitis_tarihi DATE,
    kayit_baslama DATE,
    kayit_bitis DATE,
    durum ENUM('Planlanıyor', 'Kayıt Açık', 'Başladı', 'Tamamlandı', 'İptal') DEFAULT 'Planlanıyor',
    
    -- Mali Bilgiler
    kurs_ucreti DECIMAL(8,2) DEFAULT 0.00, -- Ücretsiz kurslar için
    sertifika_ucreti DECIMAL(6,2) DEFAULT 50.00,
    
    -- Sistem Bilgileri
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    olusturan_admin VARCHAR(50),
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    FOREIGN KEY (sorumlu_egitmen_id) REFERENCES egitmenler(egitmen_id) ON DELETE SET NULL,
    FOREIGN KEY (yardimci_egitmen_id) REFERENCES egitmenler(egitmen_id) ON DELETE SET NULL
);

-- Örnek kurslar ekleme
INSERT INTO kurslar (
    kurs_adi, kurs_kodu, aciklama, kategori, seviye, yas_grubu,
    toplam_ders_saati, haftalik_ders_saati, kurs_suresi_hafta,
    on_kosul, hedef_beceriler, kullanilacak_araclar,
    sorumlu_egitmen_id, maksimum_ogrenci, minimum_ogrenci,
    baslama_tarihi, bitis_tarihi, kayit_baslama, kayit_bitis, durum,
    kurs_ucreti, sertifika_ucreti, olusturan_admin
) VALUES 

-- 1. Bilgisayar Kullanımı Kursları
(
    'Temel Bilgisayar Kullanımı - Başlangıç', 'BK-001', 
    'Bilgisayar kullanmayı hiç bilmeyen öğrenciler için temel eğitim. Bilgisayarı açma/kapama, mouse/klavye kullanımı, dosya işlemleri, internet kullanımı.',
    'Temel Bilgisayar', 'Başlangıç', '12-16 yaş',
    40, 4, 10,
    'Herhangi bir ön koşul yok', 'Bilgisayar temellerini öğrenme, Windows işletim sistemi kullanımı, Office programları tanıma',
    'Windows PC, Microsoft Office, İnternet tarayıcısı',
    1, 15, 8,
    '2024-07-01', '2024-09-09', '2024-06-01', '2024-06-25', 'Kayıt Açık',
    0.00, 30.00, 'admin'
),
(
    'İleri Bilgisayar Kullanımı', 'BK-002',
    'Word, Excel, PowerPoint kullanımı, internet araştırması, e-posta yönetimi, temel troubleshooting.',
    'Temel Bilgisayar', 'Orta', '14+ yaş',
    48, 4, 12,
    'Temel bilgisayar kullanımı bilgisi', 'Office programlarında yetkinlik, internet kullanımında güvenlik, basit problemleri çözme',
    'Microsoft Office Suite, Gmail, Antivirüs yazılımları',
    1, 12, 6,
    '2024-07-15', '2024-10-07', '2024-06-15', '2024-07-10', 'Kayıt Açık',
    0.00, 35.00, 'admin'
),

-- 2. Kodlama-Yazılım Kursları  
(
    'Kodlamaya Giriş - Scratch', 'KY-001',
    'Çocuklar için görsel programlama dili Scratch ile kodlama mantığının öğretilmesi. Oyunlar ve animasyonlar yapma.',
    'Yazılım Geliştirme', 'Başlangıç', '10-14 yaş',
    36, 3, 12,
    'Temel bilgisayar kullanımı', 'Programlama mantığı, algoritma düşüncesi, yaratıcı problem çözme',
    'Scratch 3.0, Bilgisayar, Projektör',
    1, 10, 5,
    '2024-08-01', '2024-10-24', '2024-07-01', '2024-07-25', 'Planlanıyor',
    0.00, 40.00, 'admin'
),
(
    'Python Programlama Temelleri', 'KY-002',
    'Python dili ile programlama öğretimi. Değişkenler, döngüler, fonksiyonlar, basit projeler.',
    'Yazılım Geliştirme', 'Orta', '15+ yaş',
    60, 5, 12,
    'Scratch bilgisi veya matematiksel düşünce', 'Python syntax, basit programlar yazma, dosya işlemleri, web scraping',
    'Python 3.x, PyCharm/VS Code, Git',
    1, 12, 6,
    '2024-09-01', '2024-11-24', '2024-08-01', '2024-08-25', 'Planlanıyor',
    0.00, 50.00, 'admin'
),
(
    'Web Geliştirme - HTML/CSS/JavaScript', 'KY-003',
    'Modern web siteleri oluşturma. HTML yapısı, CSS ile tasarım, JavaScript ile etkileşim.',
    'Yazılım Geliştirme', 'İleri', '16+ yaş',
    72, 6, 12,
    'Python temel bilgisi', 'Responsive web siteleri yapma, JavaScript ile dinamik içerik, basit web uygulamaları',
    'VS Code, Chrome DevTools, GitHub',
    1, 10, 5,
    '2024-10-01', '2024-12-24', '2024-09-01', '2024-09-25', 'Planlanıyor',
    0.00, 60.00, 'admin'
),

-- 3. Temel Yapay Zeka Kursları
(
    'Yapay Zeka Dünyasına Giriş', 'YZ-001',
    'AI nedir, nasıl çalışır, günlük hayatta nerede kullanılır? Temel kavramlar ve etik.',
    'Yapay Zeka', 'Başlangıç', '14+ yaş',
    24, 2, 12,
    'Temel bilgisayar kullanımı', 'AI kavramlarını anlama, ChatGPT gibi araçları kullanma, AI etiği',
    'ChatGPT, Gemini, Canva AI, Prezentasyon araçları',
    2, 15, 8,
    '2024-08-15', '2024-11-07', '2024-07-15', '2024-08-10', 'Planlanıyor',
    0.00, 35.00, 'admin'
),
(
    'Python ile Makine Öğrenmesine Giriş', 'YZ-002',
    'Python kullanarak basit makine öğrenmesi projeleri. Veri analizi, tahmin modelleri.',
    'Yapay Zeka', 'İleri', '17+ yaş',
    48, 4, 12,
    'Python programlama bilgisi', 'Pandas, NumPy kullanımı, basit ML modelleri oluşturma, veri görselleştirme',
    'Python, Jupyter Notebook, Pandas, Scikit-learn',
    2, 8, 4,
    '2024-11-01', '2024-01-24', '2024-10-01', '2024-10-25', 'Planlanıyor',
    0.00, 70.00, 'admin'
),

-- 4. Siber Güvenlik Kursları
(
    'Dijital Güvenlik ve Farkındalık', 'SG-001',
    'İnternette güvenli kalma, şifre güvenliği, sosyal medya güvenliği, dolandırıcılık türleri.',
    'Siber Güvenlik', 'Başlangıç', '13+ yaş',
    20, 2, 10,
    'Temel internet kullanımı', 'Güvenli internet kullanımı, kişisel veri koruma, sosyal mühendislik farkındalığı',
    'Web tarayıcıları, Password Manager, Antivirüs',
    3, 20, 10,
    '2024-09-15', '2024-11-24', '2024-08-15', '2024-09-10', 'Planlanıyor',
    0.00, 25.00, 'admin'
),
(
    'Etik Hacker Eğitimi - Temel', 'SG-002',
    'Beyaz şapkalı hackerlik, network güvenliği, temel penetrasyon testleri.',
    'Siber Güvenlik', 'İleri', '18+ yaş',
    80, 8, 10,
    'İleri bilgisayar kullanımı, Linux temel bilgisi', 'Network analizi, güvenlik açığı tespiti, etik hacker mentalitesi',
    'Kali Linux, Wireshark, Nmap, Metasploit',
    3, 6, 3,
    '2024-12-01', '2025-02-09', '2024-11-01', '2024-11-25', 'Planlanıyor',
    0.00, 100.00, 'admin'
);

-- Kurs bilgilerini görüntülemek için temel sorgular

-- Tüm aktif kursları kategorileriyle listele
SELECT 
    kurs_kodu,
    kurs_adi,
    kategori,
    seviye,
    yas_grubu,
    kurs_suresi_hafta,
    toplam_ders_saati,
    durum,
    baslama_tarihi
FROM kurslar 
ORDER BY kategori, seviye;

-- Kategoriye göre kurs sayıları
SELECT 
    kategori,
    COUNT(*) as kurs_sayisi,
    AVG(toplam_ders_saati) as ortalama_ders_saati
FROM kurslar 
GROUP BY kategori;

-- Kayıt açık olan kurslar
SELECT 
    kurs_kodu,
    kurs_adi,
    seviye,
    yas_grubu,
    kayit_baslama,
    kayit_bitis,
    maksimum_ogrenci
FROM kurslar 
WHERE durum = 'Kayıt Açık' 
AND CURDATE() BETWEEN kayit_baslama AND kayit_bitis
ORDER BY kayit_bitis;



-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- KAYITLAR TABLOSU

USE teknoloji_merkezi;

-- Kayıtlar tablosu (Öğrenci-Kurs ilişkisi)
CREATE TABLE kayitlar (
    kayit_id INT PRIMARY KEY AUTO_INCREMENT,
    ogrenci_id INT NOT NULL,
    kurs_id INT NOT NULL,
    
    -- Kayıt Bilgileri
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    kayit_durumu ENUM('Beklemede', 'Onaylandı', 'Reddedildi', 'İptal') DEFAULT 'Beklemede',
    onay_tarihi DATETIME,
    onaylayan_admin VARCHAR(50),
    
    -- Ön Değerlendirme
    on_degerlendirme_notu INT, -- 1-10 arası
    on_degerlendirme_yorumu TEXT,
    seviye_uygunlugu ENUM('Uygun', 'Kolay Gelir', 'Zor Gelir') DEFAULT 'Uygun',
    
    -- Kurs Süreci
    kurs_baslama_tarihi DATE,
    devam_durumu ENUM('Düzenli', 'Düzensiz', 'Bıraktı') DEFAULT 'Düzenli',
    toplam_devamsizlik INT DEFAULT 0,
    mevcut_not_ortalamasi DECIMAL(4,2),
    
    -- Tamamlanma Bilgileri
    kurs_tamamlama_durumu ENUM('Devam Ediyor', 'Başarıyla Tamamladı', 'Başarısız', 'Yarıda Bıraktı') DEFAULT 'Devam Ediyor',
    tamamlama_tarihi DATE,
    final_notu DECIMAL(4,2),
    basari_puani DECIMAL(4,2), -- Devam + Performans + Final
    
    -- Sertifika Bilgileri
    sertifika_hak_edildi ENUM('Evet', 'Hayır', 'Beklemede') DEFAULT 'Beklemede',
    sertifika_verildi ENUM('Evet', 'Hayır') DEFAULT 'Hayır',
    sertifika_tarihi DATE,
    sertifika_no VARCHAR(50),
    
    -- Mali Bilgiler
    odenen_ucret DECIMAL(8,2) DEFAULT 0.00,
    sertifika_ucreti_odendi ENUM('Evet', 'Hayır', 'Muaf') DEFAULT 'Hayır',
    odeme_tarihi DATE,
    
    -- Geri Bildirim
    ogrenci_memnuniyet_puani INT, -- 1-5 arası
    ogrenci_yorumu TEXT,
    egitmen_degerlendirmesi TEXT,
    
    -- Sistem Bilgileri
    kayit_yapan_admin VARCHAR(50),
    son_guncelleme TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_ogrenci_kurs (ogrenci_id, kurs_id),
    FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(ogrenci_id) ON DELETE CASCADE,
    FOREIGN KEY (kurs_id) REFERENCES kurslar(kurs_id) ON DELETE CASCADE
);

-- Örnek kayıtlar ekleme
INSERT INTO kayitlar (
    ogrenci_id, kurs_id, kayit_durumu, onay_tarihi, onaylayan_admin,
    on_degerlendirme_notu, on_degerlendirme_yorumu, seviye_uygunlugu,
    kurs_baslama_tarihi, devam_durumu, toplam_devamsizlik, mevcut_not_ortalamasi,
    kurs_tamamlama_durumu, basari_puani, sertifika_hak_edildi,
    odenen_ucret, sertifika_ucreti_odendi, ogrenci_memnuniyet_puani,
    ogrenci_yorumu, egitmen_degerlendirmesi, kayit_yapan_admin
) VALUES 

-- Ahmad - Temel Bilgisayar Kullanımı (Tamamladı)
(1, 1, 'Onaylandı', '2024-06-20 10:00:00', 'admin',
 7, 'Bilgisayar kullanımında temel bilgisi var, hızlı öğreniyor', 'Uygun',
 '2024-07-01', 'Düzenli', 2, 85.50,
 'Başarıyla Tamamladı', 88.75, 'Evet',
 0.00, 'Evet', 5,
 'Çok faydalı bir kurstu, artık bilgisayarı rahat kullanabiliyorum', 'Çok başarılı bir öğrenci, motivasyonu yüksek', 'admin'),

-- Ahmad - İleri Bilgisayar Kullanımı (Devam ediyor)
(1, 2, 'Onaylandı', '2024-07-10 14:30:00', 'admin',
 8, 'Temel kursu başarıyla tamamladı, Office programlarına başlayabilir', 'Uygun',
 '2024-07-15', 'Düzenli', 1, 78.25,
 'Devam Ediyor', NULL, 'Beklemede',
 0.00, 'Hayır', NULL, NULL, 'İleri seviyeye geçiş süreci iyi gidiyor', 'admin'),

-- Fatima - Temel Bilgisayar Kullanımı (Tamamladı)
(2, 1, 'Onaylandı', '2024-06-18 09:15:00', 'admin',
 5, 'Hiç bilgisayar kullanmamış, temel seviyeden başlaması gerekiyor', 'Uygun',
 '2024-07-01', 'Düzenli', 0, 92.75,
 'Başarıyla Tamamladı', 94.50, 'Evet',
 0.00, 'Muaf', 5,
 'Harika bir deneyimdi, eğitmenimiz çok sabırlıydı', 'Çok çalışkan ve başarılı, rol model olabilir', 'admin'),

-- Hassan - Python Programlama (Devam ediyor)
(3, 4, 'Onaylandı', '2024-08-20 16:00:00', 'admin',
 9, 'Daha önce programlama deneyimi var, Python için hazır', 'Kolay Gelir',
 '2024-09-01', 'Düzenli', 0, 95.00,
 'Devam Ediyor', NULL, 'Beklemede',
 0.00, 'Hayır', NULL, NULL, 'Çok yetenekli, diğer öğrencilere de yardım ediyor', 'admin'),

-- Zainab - Yapay Zeka Dünyasına Giriş (Beklemede)
(4, 6, 'Beklemede', NULL, NULL,
 6, 'Robotik konularına ilgisi var, AI kursu uygun olabilir', 'Uygun',
 NULL, NULL, 0, NULL,
 'Devam Ediyor', NULL, 'Beklemede',
 0.00, 'Hayır', NULL, NULL, NULL, 'admin'),

-- Omar - Web Geliştirme (Reddedildi - Ön koşul eksik)
(5, 5, 'Reddedildi', '2024-09-05 11:30:00', 'admin',
 4, 'Python bilgisi yeterli değil, önce Python kursunu tamamlaması gerekiyor', 'Zor Gelir',
 NULL, NULL, 0, NULL,
 'Devam Ediyor', NULL, 'Beklemede',
 0.00, 'Hayır', NULL, NULL, 'Önce Python kursunu tamamlayıp tekrar başvurmalı', 'admin'),

-- Omar - Python Programlama (Yeni kayıt)
(5, 4, 'Onaylandı', '2024-09-10 10:00:00', 'admin',
 6, 'Matematiği iyi, programlama mantığını kavrayabilir', 'Uygun',
 '2024-09-01', 'Düzenli', 1, 72.50,
 'Devam Ediyor', NULL, 'Beklemede',
 0.00, 'Hayır', NULL, NULL, 'Başlangıç zorlandı ama şimdi ilerliyor', 'admin');

-- Kayıt bilgilerini görüntülemek için temel sorgular

-- Aktif kayıtlar (Devam eden kurslar)
SELECT 
    k.kayit_id,
    CONCAT(o.ad, ' ', o.soyad) as ogrenci_adi,
    ku.kurs_adi,
    k.kayit_durumu,
    k.devam_durumu,
    k.mevcut_not_ortalamasi,
    k.toplam_devamsizlik,
    k.kurs_tamamlama_durumu
FROM kayitlar k
JOIN ogrenciler o ON k.ogrenci_id = o.ogrenci_id
JOIN kurslar ku ON k.kurs_id = ku.kurs_id
WHERE k.kurs_tamamlama_durumu = 'Devam Ediyor'
ORDER BY ku.kurs_adi, o.ad;

-- Kurs başarı istatistikleri
SELECT 
    ku.kurs_adi,
    COUNT(*) as toplam_kayit,
    SUM(CASE WHEN k.kurs_tamamlama_durumu = 'Başarıyla Tamamladı' THEN 1 ELSE 0 END) as basarili,
    SUM(CASE WHEN k.kurs_tamamlama_durumu = 'Başarısız' THEN 1 ELSE 0 END) as basarisiz,
    SUM(CASE WHEN k.kurs_tamamlama_durumu = 'Yarıda Bıraktı' THEN 1 ELSE 0 END) as birakti,
    ROUND(AVG(k.basari_puani), 2) as ortalama_basari
FROM kayitlar k
JOIN kurslar ku ON k.kurs_id = ku.kurs_id
WHERE k.kayit_durumu = 'Onaylandı'
GROUP BY ku.kurs_id, ku.kurs_adi
ORDER BY basarili DESC;

-- Sertifika durumu
SELECT 
    CONCAT(o.ad, ' ', o.soyad) as ogrenci_adi,
    ku.kurs_adi,
    k.sertifika_hak_edildi,
    k.sertifika_verildi,
    k.sertifika_tarihi,
    k.sertifika_no
FROM kayitlar k
JOIN ogrenciler o ON k.ogrenci_id = o.ogrenci_id
JOIN kurslar ku ON k.kurs_id = ku.kurs_id
WHERE k.sertifika_hak_edildi = 'Evet'
ORDER BY k.sertifika_tarihi DESC;



-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- DEVAM TABLOSU 

USE teknoloji_merkezi;

CREATE TABLE devam (
    devam_id INT PRIMARY KEY AUTO_INCREMENT,
    ogrenci_id INT NOT NULL,
    kurs_id INT NOT NULL,
    ders_tarihi DATE NOT NULL,
    katilim_durumu ENUM('Katıldı', 'Gelmedi', 'Geç Geldi', 'İzinli') DEFAULT 'Katıldı',
    aciklama TEXT, -- Devamsızlık veya geç kalma nedeni gibi açıklamalar
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    kayit_yapan_admin VARCHAR(50), -- Bu kaydı kimin girdiğini tutmak için
    
    -- Bir öğrencinin belirli bir kursta, belirli bir tarihte sadece bir devam kaydı olabilir
    UNIQUE KEY unique_devam_kaydi (ogrenci_id, kurs_id, ders_tarihi),
    
    -- Foreign Key Constraints
    FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(ogrenci_id) ON DELETE CASCADE,
    FOREIGN KEY (kurs_id) REFERENCES kurslar(kurs_id) ON DELETE CASCADE
);

-- Örnek devam verileri ekleme (isteğe bağlı, test için eklenebilir)
INSERT INTO devam (ogrenci_id, kurs_id, ders_tarihi, katilim_durumu, kayit_yapan_admin) VALUES
(1, 1, '2024-07-01', 'Katıldı', 'admin'),
(1, 1, '2024-07-08', 'Katıldı', 'admin'),
(2, 1, '2024-07-01', 'Katıldı', 'admin'),
(3, 4, '2024-09-01', 'Katıldı', 'admin'),
(5, 4, '2024-09-01', 'Gelmedi', 'admin');

---

-- Asya-yı Miyane Bilge Nesil Teknoloji Merkezi
-- Sertifikalar tablosu

USE teknoloji_merkezi;

CREATE TABLE sertifikalar (
    sertifika_id INT PRIMARY KEY AUTO_INCREMENT,
    ogrenci_id INT NOT NULL,
    kurs_id INT NOT NULL,
    sertifika_no VARCHAR(50) UNIQUE NOT NULL, -- Her sertifikanın benzersiz bir numarası olmalı
    tamamlanma_tarihi DATE NOT NULL,
    verilis_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    sertifika_onaylayan_admin VARCHAR(50),
    aciklama TEXT, -- Özel başarılar veya notlar gibi
    
    -- Bir öğrenci belirli bir kurs için sadece bir sertifika alabilir
    UNIQUE KEY unique_sertifika_ogrenci_kurs (ogrenci_id, kurs_id),
    
    -- Foreign Key Constraints
    FOREIGN KEY (ogrenci_id) REFERENCES ogrenciler(ogrenci_id) ON DELETE CASCADE,
    FOREIGN KEY (kurs_id) REFERENCES kurslar(kurs_id) ON DELETE CASCADE
);

-- Örnek sertifika verileri ekleme (isteğe bağlı, test için eklenebilir)
INSERT INTO sertifikalar (ogrenci_id, kurs_id, sertifika_no, tamamlanma_tarihi, sertifika_onaylayan_admin) VALUES
(1, 1, 'TRKTM-BK001-AHMAD-001', '2024-09-09', 'admin'),
(2, 1, 'TRKTM-BK001-FATIMA-002', '2024-09-09', 'admin');