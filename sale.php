<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sale Management Dashboard</title>
  <!-- Link to salestyle.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="salestyle.css">
  <style>
    /* Popup */
    .popup {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
      z-index: 9999; /* Ensure the popup appears above other elements */
    }

    .popup-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); /* Drop shadow effect */
    }
  </style>
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
          if ($_SESSION['role'] === 'owner' || $_SESSION['comp_staff'] == 1) {
            echo "<br>Store Name: " . $_SESSION['store_name'] . "<br>Location: " . $_SESSION['location_name'];
          }
        }
        ?>
      </div>  <a href="notifications.html"><i class="fas fa-bell"></i> Notifications</a>
      <a href="mpesa-c2b.html"><i class="fas fa-coins"></i> Mpesa C2B</a>
      <a href="mpesa-b2b.html"><i class="fas fa-exchange-alt"></i> Mpesa B2B</a>
      <a href="home.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div id="content">
      <!-- Navigation bar -->
      <div class="navbar">
        <a href="#" onclick="viewSales()">View Sales</a> <!-- Click here to view sales -->
      </div>

      <!-- Modified Input Form Card to allow multiple sales entries -->
      <div id="sales-form" class="section-card">
        <h3>Record Sales</h3>
        <form action="process_sale.php" method="POST" id="salesForm">
          <div id="salesEntries">
            <div class="entry-card">
              <label for="product_name_1">Product Name:</label>
              <select id="product_name_1" name="product_name[]" required>
                <!-- Product names will be populated dynamically using JavaScript -->
              </select>

              <label for="category_1">Category:</label>
              <select id="category_1" name="category[]" required>
                <!-- Categories will be populated dynamically using JavaScript -->
              </select>

              <label for="staff_1">Staff:</label>
              <select id="staff_1" name="staff[]" required>
                <!-- Staff names will be populated dynamically using JavaScript -->
              </select>

              <label for="quantity_sold_1">Quantity Sold:</label>
              <input type="number" id="quantity_sold_1" name="quantity_sold[]" required onchange="displayPricePopup(this)">

              <label for="total_price_1">Total Price:</label>
              <input type="number" id="total_price_1" name="total_price[]" required>
            </div>
          </div>
          <button type="button" onclick="addSalesEntry()">Add Another Entry</button>
          <button type="submit">Record Sales</button>
        </form>
      </div>
    </div>
  </div>   <!-- Select Price Popup -->
  <div class="popup" id="selectPricePopup">
    <div class="popup-content">
      <h2>Select Price</h2>
      <div id="priceSelection"></div>
    </div>
  </div>

  <!-- Include necessary JavaScript -->
  <script>
    // Include necessary JavaScript
    // Function to redirect to view sales page
    function viewSales() {
      window.location.href = 'whichsales.php';
    }

    // Function to fetch product names and categories and populate select fields
    function fetchProducts() {
      fetch('fetchproducts.php')
        .then(response => response.json())
        .then(data => {
          const productSelects = document.querySelectorAll('select[name="product_name[]"]');
          const categorySelects = document.querySelectorAll('select[name="category[]"]');

          productSelects.forEach(select => {
            select.innerHTML = ''; // Clear previous options
            data.products.forEach(product => {
              const option = document.createElement('option');
              option.value = product;
              option.textContent = product;
              select.appendChild(option);
            });
          });

          categorySelects.forEach(select => {
            select.innerHTML = ''; // Clear previous options
            data.categories.forEach(category => {
              const option = document.createElement('option');
              option.value = category;
              option.textContent = category;
              select.appendChild(option);
            });
          });
        })
        .catch(error => {
          console.error('Error fetching products:', error);
          console.error('Response:', data);
        });
    }   // Function to fetch staff names and populate select fields
    function fetchStaffNames() {   var xhr = new XMLHttpRequest();
      xhr.open('GET', 'fetchstaff2.php', true);
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
          var staffNames = JSON.parse(xhr.responseText);
          var staffDropdowns = document.querySelectorAll('select[name="staff[]"]');
          staffDropdowns.forEach(function(dropdown) {
            dropdown.innerHTML = ''; // Clear previous options
            staffNames.forEach(function(name) {
              var option = document.createElement('option');
              option.value = name;
              option.textContent = name;
              dropdown.appendChild(option);
            });
          });
        } else {
          console.error('Failed to fetch staff names');
        }
      };
      xhr.onerror = function() {
        console.error('Connection error');
      };
      xhr.send();
    }




// Function to display price popup when quantity field is populated
function displayPricePopup(input) {
  if (input.value.trim() !== '') {
    const productName = input.parentElement.querySelector('select[name="product_name[]"]').value;
    const category = input.parentElement.querySelector('select[name="category[]"]').value;
    fetchPrices(productName, category);
    document.getElementById('selectPricePopup').style.display = 'block';
  } else {
    document.getElementById('selectPricePopup').style.display = 'none';
  }
}

// Function to add new sales entry dynamically
let entryCount = 1;

function addSalesEntry() {
  entryCount++;
  const salesEntries = document.getElementById('salesEntries');

  const entryCard = document.createElement('div');
  entryCard.classList.add('entry-card');

  entryCard.innerHTML = `
    <label for="product_name_${entryCount}">Product Name:</label>
    <select id="product_name_${entryCount}" name="product_name[]" required>
      <!-- Product names will be populated dynamically using JavaScript -->
    </select>

    <label for="category_${entryCount}">Category:</label>
    <select id="category_${entryCount}" name="category[]" required>
      <!-- Categories will be populated dynamically using JavaScript -->
    </select>

    <label for="staff_${entryCount}">Staff:</label>
    <select id="staff_${entryCount}" name="staff[]" required>
      <!-- Staff names will be populated dynamically using JavaScript -->
    </select>

    <label for="quantity_sold_${entryCount}">Quantity Sold:</label>
    <input type="number" id="quantity_sold_${entryCount}" name="quantity_sold[]" required class="quantity-sold" onchange="displayPricePopup(this)">

    <label for="total_price_${entryCount}">Total Price:</label>
    <input type="number" id="total_price_${entryCount}" name="total_price[]" required class="total-price" readonly data-selling-price="0">
  `;

  salesEntries.appendChild(entryCard);

  // Fetch products and staff names for the newly added entry
  fetchProducts();
  fetchStaffNames();
}

// Call calculateTotalPrice function when the page loads
document.addEventListener('DOMContentLoaded', function() {
  calculateTotalPrice();
});

// Function to fetch prices
function fetchPrices(productName, category) {
  return fetch('fetchprices.php')
    .then(response => response.json())
    .then(data => {
      console.log(data); // Log the data to see its structure

      // Find main price for the selected product
      const mainPrice = data.mainprices.find(price => price.product_name === productName && price.category === category);

      if (mainPrice) {
        // Retrieve dynamic prices for the selected product using price_id
        const dynamicPrices = data.dynamicprices[mainPrice.price_id];

        // Populate the price selection popup with main and dynamic prices
        const priceSelection = document.getElementById('priceSelection');
        priceSelection.innerHTML = `
          <p>Select the selling price for ${productName} in ${category}</p>
          <p>Main Price: <span id="mainPrice">${mainPrice.selling_price}</span></p>
          <button onclick="selectPrice(${mainPrice.selling_price})">Main Price</button>
        `;

        if (dynamicPrices && dynamicPrices.length > 0) {
          // Append dynamic price buttons
          dynamicPrices.forEach(dynamicPrice => {
            priceSelection.innerHTML += `
              <button onclick="selectPrice(${dynamicPrice.selling_price})">Dynamic Price (${dynamicPrice.selling_price})</button>
            `;
          });
        } else {
          console.error('No dynamic prices available for the selected product and category.');
        }

        // Call calculateTotalPrice function after prices are fetched
        calculateTotalPrice();
      } else {
        console.error('Main price not found for the selected product and category.');
      }
    })
    .catch(error => {
      console.error('Error fetching prices:', error);
    });
}



// Function to select a price and close the popup
function selectPrice(price) {
  // Set the selected price in the quantity input field
  document.getElementById('total_price_1').value = price;

  // Close the popup
  document.getElementById('selectPricePopup').style.display = 'none';
}

function calculateTotalPrice() {
  // Get all entry cards
  const entryCards = document.querySelectorAll('.entry-card');

  entryCards.forEach(entryCard => {
    // Get the selected selling price for this entry
    const productNameElement = entryCard.querySelector('select[name="product_name[]"]');
    const categoryElement = entryCard.querySelector('select[name="category[]"]');
    const quantityElement = entryCard.querySelector('.quantity-sold');

    // Check if all required elements are present
    if (productNameElement && categoryElement && quantityElement) {
      const productName = productNameElement.value;
      const category = categoryElement.value;
      const quantity = parseFloat(quantityElement.value);

      fetchPrices(productName, category)
        .then(prices => {
          const sellingPrice = getSelectedSellingPrice(prices);

          // Calculate the total price for this entry
          const totalPrice = sellingPrice * quantity;

          // Insert the total price into the total_price field on this entry's form
          const totalPriceElement = entryCard.querySelector('.total-price');
          if (totalPriceElement) {
            totalPriceElement.value = totalPrice.toFixed(2);
          }
        })
        .catch(error => {
          console.error('Error fetching prices:', error);
        });
    } else {
      console.error('Required elements not found in entry card:', entryCard);
    }
  });
}

// Function to get the selected selling price from the fetched prices
function getSelectedSellingPrice(prices) {
  // Check if there are dynamic prices
  if (prices.dynamicPrices && prices.dynamicPrices.length > 0) {
    // Find the selected dynamic price
    const selectedDynamicPrice = prices.dynamicPrices.find(price => price.selected);
    if (selectedDynamicPrice) {
      return parseFloat(selectedDynamicPrice.selling_price);
    }
  }

  // If no dynamic price is selected, use the main price
  return parseFloat(prices.mainPrice.selling_price);
}
// Function to display dynamic prices when the dynamic prices button is clicked
function showDynamicPrices(dynamicPrices) {
  const priceSelection = document.getElementById('priceSelection');
  priceSelection.innerHTML += '<h3>Dynamic Prices:</h3>';
  dynamicPrices.forEach(price => {
    priceSelection.innerHTML += `
      <p>${price.category}: <span>${price.price}</span></p>
      <button onclick="calculateTotalPrice()">Select</button>
    `;
  });
}



    // Call fetchProducts and fetchStaffNames functions on page load
    document.addEventListener('DOMContentLoaded', function() {
      fetchProducts();
      fetchStaffNames();
    });
  </script>
</body>
</html>
