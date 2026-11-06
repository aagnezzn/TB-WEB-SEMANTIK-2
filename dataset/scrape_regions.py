
import os
import requests
import geopandas as gpd
import pandas as pd

# Negara Asia Tenggara
COUNTRIES = {
    "IDN": "Indonesia",
    "MYS": "Malaysia",
    "SGP": "Singapore",
    "THA": "Thailand",
    "BRN": "Brunei Darussalam",
    "PHL": "Philippines",
    "VNM": "Vietnam",
    "KHM": "Cambodia",
    "LAO": "Laos",
    "MMR": "Myanmar",
    "TLS": "Timor-Leste",
}

BASE_API = "https://www.geoboundaries.org/api/current/gbOpen"

RAW_DIR = "dataset/raw_regions"
OUTPUT_FILE = "dataset/regions_southeast_asia.csv"

os.makedirs(RAW_DIR, exist_ok=True)

all_regions = []

for iso3, country_name in COUNTRIES.items():

    print(f"\nMengambil data {country_name}...")

    api_url = f"{BASE_API}/{iso3}/ADM2/"

    try:
        response = requests.get(api_url, timeout=60)
        response.raise_for_status()

        metadata = response.json()

        # API negara tunggal mengembalikan satu object
        geojson_url = metadata.get("gjDownloadURL")

        if not geojson_url:
            print(f"URL GeoJSON tidak ditemukan: {country_name}")
            continue

        print("Mengunduh GeoJSON...")

        geo_response = requests.get(geojson_url, timeout=180)
        geo_response.raise_for_status()

        geojson_path = os.path.join(
            RAW_DIR,
            f"{iso3}_ADM2.geojson"
        )

        with open(geojson_path, "wb") as file:
            file.write(geo_response.content)

        # Membaca batas administratif
        gdf = gpd.read_file(geojson_path)

        if gdf.empty:
            print(f"Tidak ada data: {country_name}")
            continue

        # Mencari kolom nama wilayah
        possible_name_columns = [
            "shapeName",
            "NAME_2",
            "name",
            "NAME",
        ]

        name_column = next(
            (
                column
                for column in possible_name_columns
                if column in gdf.columns
            ),
            None
        )

        if name_column is None:
            print("Kolom nama wilayah tidak ditemukan.")
            print("Kolom tersedia:", list(gdf.columns))
            continue

        # Mengubah ke CRS proyeksi meter sebelum mencari
        # titik yang berada di dalam wilayah.
        projected = gdf.to_crs("EPSG:6933")

        # Representative point dijamin berada di dalam polygon.
        points = projected.geometry.representative_point()

        # Kembalikan koordinat ke WGS84 (longitude/latitude).
        points = gpd.GeoSeries(
            points,
            crs="EPSG:6933"
        ).to_crs("EPSG:4326")

        for index, row in gdf.iterrows():

            point = points.iloc[index]

            region_name = row[name_column]

            if pd.isna(region_name) or point is None:
                continue

            # Ambil ID wilayah jika tersedia
            region_id = row.get("shapeID")

            if pd.isna(region_id) or region_id is None:
                region_id = f"{iso3}-ADM2-{index + 1:04d}"

            all_regions.append({
                "region_id": region_id,
                "region_name": region_name,
                "region_type": "ADM2",
                "country_code": iso3,
                "country_name": country_name,
                "latitude": round(point.y, 7),
                "longitude": round(point.x, 7),
                "source": "geoBoundaries",
                "source_year": metadata.get(
                    "boundaryYearRepresented"
                ),
                "license": metadata.get("boundaryLicense"),
            })

        print(
            f"Berhasil mengambil {len(gdf)} wilayah "
            f"dari {country_name}"
        )

    except Exception as error:
        print(f"Gagal mengambil {country_name}: {error}")

# Membuat CSV
df = pd.DataFrame(all_regions)

if not df.empty:
    df = df.drop_duplicates(
        subset=["country_code", "region_name"]
    )

    df.to_csv(
        OUTPUT_FILE,
        index=False,
        encoding="utf-8-sig"
    )

    print("\n================================")
    print("PROSES SELESAI")
    print("Total wilayah:", len(df))
    print("File:", OUTPUT_FILE)
    print("================================")

else:
    print("Tidak ada data yang berhasil diambil.")
