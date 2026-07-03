type ProductAttributeData = {
    name?: {
        en: string;
        [locale: string]: string;
        field_type: string;
    };

    description?: {
        en: string;
        [locale: string]: string;
        field_type: string;
    };

    [key: string]: unknown;
};

export type ProductTag = {
    id: number;
    value: string;
    created_at: string;
    updated_at: string;
};

export type ProductCurrency = {
    id: number;
    code: string;
    name: string;
    exchange_rate: string;
    decimal_places: number;
    enabled: boolean;
    default: boolean;
};

export type ProductPriceData = {
    value: number;
    currency: ProductCurrency;
    unitQty: number;
};

export type ProductVariantPrice = {
    id: number;
    currency_id: number;
    price: ProductPriceData;
    compare_price: ProductPriceData;
    min_quantity: number;
    currency: ProductCurrency;
};

export type ProductOptionValue = {
    id: number;
    product_option_id: number;
    name: Record<string, string>;
    position: number;
};

export type ProductOption = {
    id: number;
    name: Record<string, string>;
    label?: Record<string, string>;
    handle?: string;
    values: ProductOptionValue[];
};

export type ProductVariantValue = {
    id: number;
    product_option_id: number;
    name: Record<string, string>;
    option?: ProductOption;
};

export type ProductVariant = {
    id: number;
    product_id: number;
    sku: string;
    stock: number;
    backorder: number;
    purchasable: 'in_stock' | 'backorder' | 'out_stock' | string;
    shippable: number;
    prices: ProductVariantPrice[];
    resolved_price: ProductVariantPrice;
    values?: ProductVariantValue[];
};

export type ProductBrand = {
    id: number;
    name?: string;
    attribute_data?: { name?: { en: string } };
};

export type Product = {
    id: number;
    brand_id: number | null;
    product_type_id: number;
    status: 'published' | 'draft' | string;
    attribute_data: ProductAttributeData;
    tags: ProductTag[];
    medium_images_urls: string[];
    small_image_url: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    variants?: ProductVariant[];
    brand?: ProductBrand | null;
    product_options?: ProductOption[];
};
