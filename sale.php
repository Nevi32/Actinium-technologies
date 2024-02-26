<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Management Dashboard</title>
  <!-- Link to font-awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="salestyle.css"> <!-- Adjusted CSS file -->
</head>
<body>
  <div id="sales-page">
    <div id="sidebar">
      <!-- Sidebar content -->
      <a href="#" id="user-icon"><i class="fas fa-user"></i> User</a>
      <div id="user-info">
        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
          echo "User ID: " . $_SESSION['user_id'] . "<br>Username: " . $_SESSION['username'] . "<br>Role: " . $_SESSION['role'];
        }
        ?>
      </div>
      <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
      <a href="add_product.html"><i class="fas fa-plus"></i> Add Product</a>
      <a href="home.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
      <!-- Navigation bar -->
      <div class="navbar">
        <a href="#" onclick="viewSales()">View Sales</a> <!-- View Sales button -->
      </div>

        <!-- Sales Form Card -->
<div id="sales-form" class="section-card">
    <h3>Record Sales</h3>
    <form id="salesForm">
        <div id="salesEntries">
            <div class="entry-card" id="entry_1">
                <label for="product_name_1">Product Name:</label>
                <select id="product_name_1" name="product_name[]" onchange="fetchPrices(this.value, document.getElementById('category_1').value, 1)" required></select>

                <label for="category_1">Category:</label>
                <select id="category_1" name="category[]" onchange="fetchPrices(document.getElementById('product_name_1').value, this.value, 1)" required></select>

                <label for="staff_1">Staff:</label>
                <select id="staff_1" name="staff[]" required></select>

                <label for="quantity_sold_1">Quantity Sold:</label>
                <input type="number" id="quantity_sold_1" name="quantity_sold[]" onchange="fetchPrices(document.getElementById('product_name_1').value, document.getElementById('category_1').value, 1)" required>

                <label for="total_price_1">Total Price:</label>
                <input type="number" id="total_price_1" name="total_price[]" required>
            </div>
        </div>
        <button type="button" onclick="addSalesEntry()">Add Another Entry</button>
        <button type="button" onclick="recordSales()">Record Sales</button>
    </form>
</div>


      <!-- Popup for Receipt -->
      <div id="receiptPopup" class="popup">
        <div id="receiptContent"></div>
        <button onclick="closeReceipt()">Close</button>
      </div>

      <!-- Popup for Prices -->
      <div id="pricesPopup" class="popup">
        <div id="pricesContent"></div>
        <button onclick="closePricesPopup()">Close</button>
      </div>
    </div>
  </div>

  <!-- Include necessary JavaScript -->
  <script>
    // Function to redirect to view sales page
    function viewSales() {
        // Redirect to whichsales.php
        window.location.href = 'whichsales.php';
    }

    // Function to add new sales entry dynamically
let salesEntryCount = 1;

function addSalesEntry() {
  const entryCount = ++salesEntryCount;
  const salesEntries = document.getElementById('salesEntries');

  const entryCard = document.createElement('div');
  entryCard.classList.add('entry-card');

  entryCard.innerHTML = `
    <label for="product_name_${entryCount}">Product Name:</label>
    <select id="product_name_${entryCount}" name="product_name[]" onchange="fetchPrices(this.value, document.getElementById('category_${entryCount}').value, ${entryCount})" required></select>

    <label for="category_${entryCount}">Category:</label>
    <select id="category_${entryCount}" name="category[]" onchange="fetchPrices(document.getElementById('product_name_${entryCount}').value, this.value, ${entryCount})" required></select>

    <label for="staff_${entryCount}">Staff:</label>
    <select id="staff_${entryCount}" name="staff[]" required></select>

    <label for="quantity_sold_${entryCount}">Quantity Sold:</label>
    <input type="number" id="quantity_sold_${entryCount}" name="quantity_sold[]" onchange="fetchPrices(document.getElementById('product_name_${entryCount}').value, document.getElementById('category_${entryCount}').value, ${entryCount})" required>

    <label for="total_price_${entryCount}">Total Price:</label>
    <input type="number" id="total_price_${entryCount}" name="total_price[]" required>
  `;

  salesEntries.appendChild(entryCard);
  fetchProducts(`product_name_${entryCount}`, `category_${entryCount}`);
  fetchStaff(`staff_${entryCount}`);
}

   // Fetch products, categories, and staff for the first entry when the page loads
window.onload = function() {
    fetchProducts('product_name_1', 'category_1');
    fetchStaff('staff_1');
    calculateTotalPrice('initial_value', 1); // You might need to replace 'initial_value' with the default value for the first entry
};
    // Function to record sales
    function recordSales() {
      // AJAX request to process_sales.php
      var formData = new FormData(document.getElementById('salesForm'));

      // AJAX request
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'process_sales.php', true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          // showAlert(xhr.responseText, 'success'); // Pass response text to showAlert function
          console.log(xhr.responseText); // Log response to console
          
          // Display receipt popup with content from response
          displayReceiptPopup(xhr.responseText);
        } else {
          // showAlert('Failed to record sales. Please try again.', 'error');
          console.log('Failed to record sales. Please try again.'); // Log error message to console
        }
      };
      xhr.send(formData);
    }

    // Function to display receipt popup with content
    function displayReceiptPopup(receiptContent) {
      const receiptPopup = document.getElementById('receiptPopup');
      const receiptContentDiv = document.getElementById('receiptContent');
      receiptContentDiv.innerHTML = receiptContent;
      receiptPopup.style.display = 'block';
    }

    // Function to close receipt popup
    function closeReceipt() {
      const receiptPopup = document.getElementById('receiptPopup');
      receiptPopup.style.display = 'none';
    }

    // Function to fetch products and populate select options
    function fetchProducts(productSelectId, categorySelectId) {
      fetch('fetchproducts.php')
        .then(response => response.json())
        .then(data => {
          const productSelect = document.getElementById(productSelectId);
          const categorySelect = document.getElementById(categorySelectId);

          data.products.forEach(product => {
            const option = document.createElement('option');
            option.value = product;
            option.textContent = product;
            productSelect.appendChild(option);
          });

          data.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
          });
        })
        .catch(error => console.log('Error fetching products:', error));
    }

    // Function to fetch staff names and populate select options
    function fetchStaff(staffSelectId) {
      fetch('fetchstaff2.php')
        .then(response => response.json())
        .then(data => {
          const staffSelect = document.getElementById(staffSelectId);

          data.forEach(staff => {
            const option = document.createElement('option');
            option.value = staff;
            option.textContent = staff;
            staffSelect.appendChild(option);
          });
        })
        .catch(error => console.log('Error fetching staff:', error));
    }

     // Function to fetch prices based on product name, category, and entry count
function fetchPrices(productName, category, entryCount) {
    const quantitySold = document.getElementById(`quantity_sold_${entryCount}`).value;
    if (quantitySold !== '') { // Only fetch prices if quantity sold is not empty
        fetch('fetchprices.php')
            .then(response => response.json())
            .then(data => {
                displayPricesPopup(data, productName, category, entryCount, quantitySold);
            })
            .catch(error => console.error('Error fetching prices:', error));
    }
}

// Function to display prices popup with content for a specific entry
function displayPricesPopup(data, productName, category, entryCount, quantitySold) {
    const pricesPopup = document.getElementById('pricesPopup');
    const pricesContentDiv = document.getElementById('pricesContent');
    let priceContent = `<h2>Select the selling price for ${productName} and ${category}</h2>`;
    const mainPrice = data.mainprices.find(price => price.product_name === productName && price.category === category);
    
    if (mainPrice) {
        priceContent += `<p>Main Price: <span onclick="calculateTotalPrice('${mainPrice.selling_price}', ${entryCount})">${mainPrice.selling_price}</span></p>`;
    }
    if (data.dynamicprices.hasOwnProperty(mainPrice.price_id)) {
        priceContent += '<p>Dynamic Prices:</p><ul>';
        data.dynamicprices[mainPrice.price_id].forEach(dynamicPrice => {
            priceContent += `<li><span onclick="calculateTotalPrice('${dynamicPrice.selling_price}', ${entryCount})">${dynamicPrice.selling_price}</span></li>`;
        });
        priceContent += '</ul>';
    }

    pricesContentDiv.innerHTML = priceContent;
    pricesPopup.style.display = 'block';

    // Add event listener to the total price input field
    const totalPriceInput = document.getElementById(`total_price_${entryCount}`);
    totalPriceInput.addEventListener('focus', () => {
        if (totalPriceInput.value.trim() === '') { // Check if total price field is empty
            closePricesPopup(); // Close the prices popup if the total price field is empty
        }
    });
}

 // Function to close prices popup
    function closePricesPopup() {
      const pricesPopup = document.getElementById('pricesPopup');
      pricesPopup.style.display = 'none';
    }

    // Function to calculate total price for a specific entry
function calculateTotalPrice(price, entryCount) {
  const quantitySold = document.getElementById(`quantity_sold_${entryCount}`).value;
  const totalPriceField = document.getElementById(`total_price_${entryCount}`);
  
  // Check if the total price field exists
  if (totalPriceField) {
    totalPriceField.value = quantitySold * price;
    closePricesPopup();
  } else {
    console.error(`Total price field for entry ${entryCount} does not exist.`);
  }
}

  </script>
</body>
</html>

