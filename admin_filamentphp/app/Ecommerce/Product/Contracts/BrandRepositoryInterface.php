<?php

namespace App\Ecommerce\Product\Contracts;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    // Specific brand queries can be added here
    // Hiện tại không được áp dụng thì nên xoá đi 
    // Repository chỉ áp dụng đối với các dự án lớn với các SQL phức tạp hoặc các truy vấn cần tối ưu hóa hiệu suất
}
