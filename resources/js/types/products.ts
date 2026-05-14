type ProductAttributeData = {
  name?: {
    en: string,
    [locale: string]: string,
    field_type: string,
  };

  description?: {
    en: string,
    [locale: string]: string,
    field_type: string,
  };

  [key: string]: unknown;
};

export type ProductTag = {
  id: number;
  value: string;
  created_at: string;
  updated_at: string;
};

export type Product = {
  id: number;
  brand_id: number | null;
  product_type_id: number;
  status: "published" | "draft" | string;
  attribute_data: ProductAttributeData;
  tags: ProductTag[];
  small_image_url: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
};
