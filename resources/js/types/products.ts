type ProductAttributeData = {
  name?: {
    value: {
      en: string;
      [locale: string]: string;
    };
    field_type: string;
  };

  description?: {
    value: {
      en: string;
      [locale: string]: string;
    };
    field_type: string;
  };

  [key: string]: unknown;
};

export type Product = {
  id: number;
  brand_id: number | null;
  product_type_id: number;
  status: "published" | string;
  attribute_data: ProductAttributeData;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
};
