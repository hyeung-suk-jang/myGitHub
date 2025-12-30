/**
 * Google Maps 통합 JavaScript
 * API 키는 환경변수나 설정 파일에서 관리해야 합니다
 */

// Google Maps API 키 (실제 환경에서는 환경변수로 관리)
const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY'; // 실제 키로 교체 필요

let map = null;
let markers = [];
let geocoder = null;
let autocomplete = null;

/**
 * Google Maps 초기화
 */
function initMap(elementId, options = {}) {
    const defaultOptions = {
        center: { lat: 37.5665, lng: 126.9780 }, // 서울 시청
        zoom: 12,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    };

    const mapOptions = { ...defaultOptions, ...options };
    const mapElement = document.getElementById(elementId);

    if (!mapElement) {
        console.error('Map element not found:', elementId);
        return null;
    }

    map = new google.maps.Map(mapElement, mapOptions);
    geocoder = new google.maps.Geocoder();

    return map;
}

/**
 * 주소를 좌표로 변환
 */
async function geocodeAddress(address) {
    return new Promise((resolve, reject) => {
        if (!geocoder) {
            geocoder = new google.maps.Geocoder();
        }

        geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;
                resolve({
                    lat: location.lat(),
                    lng: location.lng(),
                    formatted_address: results[0].formatted_address
                });
            } else {
                reject(new Error('Geocoding failed: ' + status));
            }
        });
    });
}

/**
 * 좌표를 주소로 변환
 */
async function reverseGeocode(lat, lng) {
    return new Promise((resolve, reject) => {
        if (!geocoder) {
            geocoder = new google.maps.Geocoder();
        }

        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status === 'OK' && results[0]) {
                resolve(results[0].formatted_address);
            } else {
                reject(new Error('Reverse geocoding failed: ' + status));
            }
        });
    });
}

/**
 * 마커 추가
 */
function addMarker(position, options = {}) {
    if (!map) {
        console.error('Map not initialized');
        return null;
    }

    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: options.title || '',
        icon: options.icon || null,
        animation: options.animation || null
    });

    // 인포윈도우 추가
    if (options.infoWindow) {
        const infoWindow = new google.maps.InfoWindow({
            content: options.infoWindow
        });

        marker.addListener('click', () => {
            // 다른 인포윈도우 닫기
            markers.forEach(m => {
                if (m.infoWindow) {
                    m.infoWindow.close();
                }
            });
            infoWindow.open(map, marker);
        });

        marker.infoWindow = infoWindow;
    }

    markers.push(marker);
    return marker;
}

/**
 * 모든 마커 제거
 */
function clearMarkers() {
    markers.forEach(marker => {
        marker.setMap(null);
    });
    markers = [];
}

/**
 * 장비 위치 마커 추가
 */
async function addEquipmentMarkers(equipmentList) {
    clearMarkers();

    for (const equipment of equipmentList) {
        if (!equipment.location) continue;

        try {
            const location = await geocodeAddress(equipment.location);

            const imageUrl = getImageUrl(equipment.primary_image);
            const infoContent = `
                <div style="max-width: 250px;">
                    <img src="${imageUrl}" style="width: 100%; height: 150px; object-fit: cover; margin-bottom: 0.5rem;" />
                    <h4 style="margin: 0.5rem 0;">${equipment.equipment_name}</h4>
                    <p style="margin: 0.25rem 0; color: #666;">${equipment.location}</p>
                    <p style="margin: 0.25rem 0; font-weight: bold; color: #2563eb;">
                        ${formatNumber(equipment.daily_rate)}원/일
                    </p>
                    <a href="equipment-detail.html?id=${equipment.equipment_id}"
                       style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background: #2563eb; color: white; text-decoration: none; border-radius: 4px;">
                        상세보기
                    </a>
                </div>
            `;

            addMarker(
                { lat: location.lat, lng: location.lng },
                {
                    title: equipment.equipment_name,
                    infoWindow: infoContent,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                        scaledSize: new google.maps.Size(40, 40)
                    }
                }
            );
        } catch (error) {
            console.error('Failed to geocode:', equipment.location, error);
        }
    }

    // 모든 마커가 보이도록 지도 범위 조정
    if (markers.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });
        map.fitBounds(bounds);
    }
}

/**
 * 주소 자동완성 설정
 */
function setupAutocomplete(inputId, options = {}) {
    const input = document.getElementById(inputId);
    if (!input) {
        console.error('Input element not found:', inputId);
        return null;
    }

    const autocompleteOptions = {
        componentRestrictions: { country: 'kr' }, // 한국으로 제한
        fields: ['formatted_address', 'geometry', 'name'],
        ...options
    };

    autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();

        if (!place.geometry) {
            console.error('No geometry for place:', place.name);
            return;
        }

        // 콜백 함수 호출
        if (options.onPlaceSelected) {
            options.onPlaceSelected(place);
        }

        // 지도가 있으면 해당 위치로 이동
        if (map) {
            map.setCenter(place.geometry.location);
            map.setZoom(15);

            // 마커 추가
            clearMarkers();
            addMarker(place.geometry.location, {
                title: place.formatted_address,
                animation: google.maps.Animation.DROP
            });
        }
    });

    return autocomplete;
}

/**
 * 현재 위치 가져오기
 */
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation is not supported'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            },
            (error) => {
                reject(error);
            }
        );
    });
}

/**
 * 현재 위치로 지도 이동
 */
async function moveToCurrentLocation() {
    try {
        const location = await getCurrentLocation();

        if (map) {
            map.setCenter(location);
            map.setZoom(14);

            clearMarkers();
            addMarker(location, {
                title: '현재 위치',
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                }
            });
        }

        return location;
    } catch (error) {
        console.error('Failed to get current location:', error);
        throw error;
    }
}

/**
 * 두 지점 간 거리 계산 (km)
 */
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // 지구 반지름 (km)
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);

    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
              Math.sin(dLng / 2) * Math.sin(dLng / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return distance;
}

function toRad(degrees) {
    return degrees * (Math.PI / 180);
}

/**
 * 근처 장비 찾기
 */
async function findNearbyEquipment(userLat, userLng, maxDistance = 10) {
    // 서버에서 모든 장비 가져오기
    const result = await api.get('equipment.php?action=list', { limit: 100 });

    if (!result.success) {
        throw new Error('Failed to fetch equipment');
    }

    const nearbyEquipment = [];

    for (const equipment of result.data.equipment) {
        if (!equipment.location) continue;

        try {
            const location = await geocodeAddress(equipment.location);
            const distance = calculateDistance(
                userLat,
                userLng,
                location.lat,
                location.lng
            );

            if (distance <= maxDistance) {
                nearbyEquipment.push({
                    ...equipment,
                    distance: distance.toFixed(2),
                    lat: location.lat,
                    lng: location.lng
                });
            }
        } catch (error) {
            console.error('Failed to process equipment:', equipment.equipment_id, error);
        }
    }

    // 거리순 정렬
    nearbyEquipment.sort((a, b) => parseFloat(a.distance) - parseFloat(b.distance));

    return nearbyEquipment;
}

/**
 * Google Maps API 스크립트 동적 로드
 */
function loadGoogleMapsScript(callback) {
    if (window.google && window.google.maps) {
        callback();
        return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=places&language=ko`;
    script.async = true;
    script.defer = true;
    script.onload = callback;
    script.onerror = () => {
        console.error('Failed to load Google Maps API');
        showAlert('Google Maps를 로드할 수 없습니다. API 키를 확인해주세요.', 'error');
    };

    document.head.appendChild(script);
}
