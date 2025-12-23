<?php
/**
 * AutoProvince Class
 * ระบบดึงข้อมูลจังหวัด อำเภอ ตำบล สำหรับ eDonation
 * 
 * Tables (renamed with edonation_ prefix):
 * - edonation_provinces
 * - edonation_districts  
 * - edonation_subdistricts
 * 
 * @version 2.0 - eDonation Edition
 */

class AutoProvince {
    private PDO $pdo;
    
    /**
     * Constructor - accepts PDO connection
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all provinces
     * @return array List of provinces
     */
    public function getProvinces(): array {
        $stmt = $this->pdo->query(
            "SELECT id, name FROM edonation_provinces ORDER BY name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get districts by province ID
     * @param int $provinceId
     * @return array List of districts
     */
    public function getDistricts(int $provinceId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, name FROM edonation_districts WHERE province_id = :province_id ORDER BY name ASC"
        );
        $stmt->execute([':province_id' => $provinceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get subdistricts by district ID
     * @param int $districtId
     * @return array List of subdistricts with postcode
     */
    public function getSubdistricts(int $districtId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, postcode FROM edonation_subdistricts WHERE district_id = :district_id ORDER BY name ASC"
        );
        $stmt->execute([':district_id' => $districtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get full address parts by subdistrict ID
     * @param int $subdistrictId
     * @return array|null Full address info
     */
    public function getFullAddress(int $subdistrictId): ?array {
        $sql = "SELECT 
                    s.id as subdistrict_id,
                    s.name as subdistrict_name,
                    s.postcode,
                    d.id as district_id,
                    d.name as district_name,
                    p.id as province_id,
                    p.name as province_name
                FROM edonation_subdistricts s
                JOIN edonation_districts d ON s.district_id = d.id
                JOIN edonation_provinces p ON d.province_id = p.id
                WHERE s.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $subdistrictId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Format full address string
     * @param int $subdistrictId
     * @return string Formatted address
     */
    public function formatAddress(int $subdistrictId): string {
        $address = $this->getFullAddress($subdistrictId);
        if (!$address) return '';
        
        return sprintf(
            "ต.%s อ.%s จ.%s %s",
            $address['subdistrict_name'],
            $address['district_name'],
            $address['province_name'],
            $address['postcode']
        );
    }
    
    /**
     * Search addresses by keyword
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public function search(string $keyword, int $limit = 20): array {
        $keyword = "%{$keyword}%";
        
        $sql = "SELECT 
                    s.id as subdistrict_id,
                    s.name as subdistrict_name,
                    s.postcode,
                    d.name as district_name,
                    p.name as province_name
                FROM edonation_subdistricts s
                JOIN edonation_districts d ON s.district_id = d.id
                JOIN edonation_provinces p ON d.province_id = p.id
                WHERE s.name LIKE :keyword 
                   OR d.name LIKE :keyword 
                   OR p.name LIKE :keyword
                   OR s.postcode LIKE :keyword
                ORDER BY p.name, d.name, s.name
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate HTML select options for provinces
     * @param int|null $selectedId
     * @return string HTML options
     */
    public function getProvinceOptions(?int $selectedId = null): string {
        $provinces = $this->getProvinces();
        $html = '<option value="">-- เลือกจังหวัด --</option>';
        
        foreach ($provinces as $province) {
            $selected = ($selectedId == $province['id']) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $province['id'],
                $selected,
                htmlspecialchars($province['name'])
            );
        }
        
        return $html;
    }
}
