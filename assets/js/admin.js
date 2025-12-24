jQuery(document).ready(function($) {
    
    // Design Tab - Color Picker
    if ($('.ips-color-picker').length) {
        $('.ips-color-picker').wpColorPicker();
    }
    
    // Design Tab - Save Settings
    $('#ips-design-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'ips_save_settings',
            nonce: ipsAdmin.nonce,
            tag_bg_color: $('#tag_bg_color').val(),
            icon_color: $('#icon_color').val(),
            button_text: $('#button_text').val()
        };
        
        $.post(ipsAdmin.ajax_url, formData, function(response) {
            showMessage(response.data.message, response.success ? 'success' : 'error');
        });
    });
    
    // Add Image Tab - Image Selection
    var mediaUploader;
    
    $('#ips-select-image').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#showcase_image_url').val(attachment.url);
            $('#ips-image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">');
            $('#ips-hotspot-editor').show();
            initializeImageContainer(attachment.url);
        });
        
        mediaUploader.open();
    });
    
    // Initialize image container with hotspots
    function initializeImageContainer(imageUrl) {
        var container = $('#ips-image-container');
        container.html('<img id="ips-main-image" src="' + imageUrl + '" style="max-width: 100%; height: auto;">');
        
        // Load existing hotspots
        var hotspotsData = $('#hotspots_data').val();
        if (hotspotsData) {
            try {
                var hotspots = JSON.parse(hotspotsData);
                hotspots.forEach(function(hotspot, index) {
                    addHotspotMarker(hotspot.x, hotspot.y, index);
                });
            } catch(e) {
                console.error('Error parsing hotspots:', e);
            }
        }
    }
    
    // Add hotspot on image click
    var currentHotspotIndex = null;
    
    $(document).on('click', '#ips-main-image', function(e) {
        var offset = $(this).offset();
        var x = ((e.pageX - offset.left) / $(this).width() * 100);
        var y = ((e.pageY - offset.top) / $(this).height() * 100);
        
        currentHotspotIndex = $('.ips-hotspot-marker').length;
        
        // Show product selection modal
        $('#ips-product-modal').data('x', x).data('y', y).fadeIn();
        $('#ips-product-search-input').focus();
    });
    
    // Add hotspot marker to image
    function addHotspotMarker(x, y, index) {
        var marker = $('<div class="ips-hotspot-marker" data-index="' + index + '" style="position: absolute; left: ' + x + '%; top: ' + y + '%;">' +
            '<span class="ips-hotspot-number">' + (index + 1) + '</span>' +
            '<button type="button" class="ips-remove-hotspot">×</button>' +
            '</div>');
        
        $('#ips-image-container').append(marker);
        
        // Make marker draggable
        marker.draggable({
            containment: '#ips-main-image',
            stop: function(event, ui) {
                updateHotspotPosition($(this));
            }
        });
    }
    
    // Update hotspot position
    function updateHotspotPosition(marker) {
        var container = $('#ips-main-image');
        var containerOffset = container.offset();
        var markerOffset = marker.offset();
        
        var x = ((markerOffset.left - containerOffset.left + marker.width()/2) / container.width() * 100);
        var y = ((markerOffset.top - containerOffset.top + marker.height()/2) / container.height() * 100);
        
        var index = marker.data('index');
        var hotspotsData = JSON.parse($('#hotspots_data').val() || '[]');
        
        if (hotspotsData[index]) {
            hotspotsData[index].x = x;
            hotspotsData[index].y = y;
            $('#hotspots_data').val(JSON.stringify(hotspotsData));
        }
    }
    
    // Remove hotspot
    $(document).on('click', '.ips-remove-hotspot', function(e) {
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to remove this hotspot?')) {
            return;
        }
        
        var marker = $(this).closest('.ips-hotspot-marker');
        var index = marker.data('index');
        
        // Remove from data
        var hotspotsData = JSON.parse($('#hotspots_data').val() || '[]');
        hotspotsData.splice(index, 1);
        $('#hotspots_data').val(JSON.stringify(hotspotsData));
        
        // Remove marker
        marker.remove();
        
        // Reindex remaining markers
        $('.ips-hotspot-marker').each(function(i) {
            $(this).data('index', i);
            $(this).find('.ips-hotspot-number').text(i + 1);
        });
    });
    
    // Product Search
    $('#ips-product-search-btn').on('click', function() {
        searchProducts();
    });
    
    $('#ips-product-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchProducts();
        }
    });
    
    function searchProducts() {
        var searchTerm = $('#ips-product-search-input').val();
        
        if (!searchTerm) {
            alert(ipsAdmin.strings.select_product);
            return;
        }
        
        $.post(ipsAdmin.ajax_url, {
            action: 'ips_search_products',
            nonce: ipsAdmin.nonce,
            search: searchTerm
        }, function(response) {
            if (response.success) {
                displayProductResults(response.data);
            } else {
                alert(response.data.message);
            }
        });
    }
    
    function displayProductResults(products) {
        var html = '';
        
        if (products.length === 0) {
            html = '<p style="text-align: center; padding: 20px;">No products found.</p>';
        } else {
            products.forEach(function(product) {
                html += '<div class="ips-product-item" data-product-id="' + product.id + '" data-product-title="' + product.title + '" data-product-image="' + product.image + '" data-product-url="' + product.url + '" data-product-description="' + product.description + '">';
                html += '<img src="' + (product.image || ipsAdmin.placeholder) + '" alt="' + product.title + '">';
                html += '<h4>' + product.title + '</h4>';
                html += '</div>';
            });
        }
        
        $('#ips-product-results').html(html);
    }
    
    // Product selection
    $(document).on('click', '.ips-product-item', function() {
     $('.ips-product-item').removeClass('selected');
     $(this).addClass('selected');
     
     var productId = $(this).data('product-id');
     var productTitle = $(this).data('product-title');
     var productImage = $(this).data('product-image');
     var productUrl = $(this).data('product-url');
     
     $('#selected_product_id').val(productId);
     $('#selected_product_title').text(productTitle);
     $('#selected_product_image').attr('src', productImage);
     $('#product_description').val(''); // Boş bırak
     
     $('#ips-product-details').show();
    });
    
    // Save hotspot
    $('#ips-save-hotspot').on('click', function() {
        var productId = $('#selected_product_id').val();
        
        if (!productId) {
            alert(ipsAdmin.strings.select_product);
            return;
        }
        
        var x = $('#ips-product-modal').data('x');
        var y = $('#ips-product-modal').data('y');
        
        var hotspotData = {
            x: x,
            y: y,
            product_id: productId,
            product_title: $('#selected_product_title').text(),
            product_image: $('#selected_product_image').attr('src'),
            product_url: $('.ips-product-item.selected').data('product-url'),
            description: $('#product_description').val()
        };
        
        // Add to hotspots data
        var hotspotsData = JSON.parse($('#hotspots_data').val() || '[]');
        hotspotsData.push(hotspotData);
        $('#hotspots_data').val(JSON.stringify(hotspotsData));
        
        // Add marker
        addHotspotMarker(x, y, hotspotsData.length - 1);
        
        // Close modal
        closeProductModal();
    });
    
    // Close modal
    $('.ips-modal-close').on('click', function() {
        closeProductModal();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('ips-modal')) {
            closeProductModal();
        }
    });
    
    function closeProductModal() {
        $('#ips-product-modal').fadeOut();
        $('#ips-product-search-input').val('');
        $('#ips-product-results').html('');
        $('#ips-product-details').hide();
        $('.ips-product-item').removeClass('selected');
    }
    
    // Save showcase
    $('#ips-showcase-form').on('submit', function(e) {
        e.preventDefault();
        
        var title = $('#showcase_title').val();
        var imageUrl = $('#showcase_image_url').val();
        var hotspotsData = $('#hotspots_data').val();
        
        if (!title || !imageUrl) {
            alert('Please provide a title and select an image.');
            return;
        }
        
        var formData = {
            action: 'ips_save_showcase',
            nonce: ipsAdmin.nonce,
            showcase_id: $('#showcase_id').val(),
            title: title,
            image_url: imageUrl,
            hotspots: hotspotsData
        };
        
        $.post(ipsAdmin.ajax_url, formData, function(response) {
            if (response.success) {
                showMessage(response.data.message + '<br>Shortcode: <code>' + response.data.shortcode + '</code>', 'success');
                
                // Update URL to include edit parameter
                if (!$('#showcase_id').val()) {
                    window.location.href = window.location.href + '&edit=' + response.data.showcase_id;
                }
            } else {
                showMessage(response.data.message, 'error');
            }
        });
    });
    
    // Delete showcase
    $(document).on('click', '.ips-delete-showcase', function() {
        if (!confirm(ipsAdmin.strings.confirm_delete)) {
            return;
        }
        
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.post(ipsAdmin.ajax_url, {
            action: 'ips_delete_showcase',
            nonce: ipsAdmin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                row.fadeOut(function() {
                    $(this).remove();
                });
            } else {
                alert(response.data.message);
            }
        });
    });
    
    // Copy shortcode
    $(document).on('click', '.ips-copy-shortcode', function() {
        var shortcode = $(this).data('shortcode');
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        $(this).text('Copied!');
        var btn = $(this);
        setTimeout(function() {
            btn.text('Copy');
        }, 2000);
    });
    
    // Show message helper
    function showMessage(message, type) {
        var messageDiv = $('#ips-message');
        messageDiv.removeClass('notice-success notice-error')
                  .addClass('notice-' + type)
                  .html('<p>' + message + '</p>')
                  .fadeIn();
        
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 5000);
    }
    
});