const movies = [
  { title: "A Minecraft Movie", desc: "An epic adventure in the blocky world of Minecraft. Join Steve and Alex as they save their village!", img: "minecraft.jpg", seats: 50, price: [300, 450, 600] },
  { title: "Snow White", desc: "A magical retelling of the classic fairy tale with new twists and stunning visuals.", img: "Snowwhite.jpg", seats: 40, price: [450, 675, 900] },
  { title: "My Love Make You Disappear", desc: "A romantic drama where love and magic collide, making people vanish in the blink of an eye.", img: "mylove.jpg", seats: 30, price: [250, 375, 500] },
  { title: "Kaiju no.8", desc: "Earth faces giant monsters! Humanity's last hope is the elite Kaiju No.8 squad.", img: "kaiju8.jpg", seats: 20, price: [200, 300, 400] }
];
const cinemaTypes = ["Standard Cinema", "IMAX Cinema", "Director's Club"];
const showTimes = [
  ["10:00 AM", "1:00 PM", "4:00 PM"],
  ["11:00 AM", "2:00 PM", "5:00 PM"],
  ["12:00 PM", "3:00 PM", "6:00 PM"],
  ["10:30 AM", "1:30 PM", "4:30 PM"]
];
const foodItems = [
  { name: "Hotdog Sandwich", price: 70 },
  { name: "Pretzels", price: 90 },
  { name: "Popcorn", price: 120 },
  { name: "Drinks", price: 40 }
];

// --- State ---
let users = { admin: "admin123" };
let currentUser = null;
let booking = {
  movie: 0,
  cinema: 0,
  showtime: 0,
  tickets: 1,
  seats: [],
  food: [0, 0, 0, 0]
};

// --- Panel Switching ---
function showPanel(panel) {
    document.querySelectorAll('#app > section').forEach(sec => sec.classList.add('hidden'));
    const activePanel = document.getElementById(panel + '-panel');
    if (activePanel) activePanel.classList.remove('hidden');
    const nav = document.getElementById('nav');
    if (panel === 'login' || panel === 'register') {
        nav.classList.add('hidden');
    } else {
        nav.classList.remove('hidden');
    }
    if (panel === 'movies') renderMovies();
    if (panel === 'book') renderBook();
    if (panel === 'food') renderFood();
    if (panel === 'payment') renderPayment();
    if (panel === 'history') renderHistory();
}

document.addEventListener('DOMContentLoaded', function() {
    showPanel('login'); // or 'welcome' if you want welcome first
});

// Example logout function
function logout() {
   fetch('logout.php',)
      .then(() => {
        //Reset login form
        const form = document.getElementById('login-form');
        if (form) form.reset();

        //Clear login message
        const msg = document.getElementById("login-msg");
        if (msg) msg.textContent = "";

        //clear booking state too
        booking = {
          movie: 0, cinema: 0, showtime: 0, tickets: 1, seats: [], food: [0, 0, 0, 0]
        };

        //Show login panel
        showPanel('login');
      })
      .catch(err => console.error('Logout failed', err));
}

// --- Auth (fixed AJAX syntax) ---
const loginForm = document.getElementById('login-form');
const loginMsg = document.getElementById('login-msg');
if (loginForm) {
    loginForm.onsubmit = function(e) {
        e.preventDefault();
        loginMsg.textContent = '';
        document.getElementById('login-btn').disabled = true;
        const formData = new FormData(loginForm);
        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('login-btn').disabled = false;
            if (data.success) {
                loginMsg.style.color = 'green';
                loginMsg.textContent = 'Login successful! Redirecting...';
                setTimeout(() => showPanel('movies'), 1000);
            } else {
                loginMsg.style.color = 'red';
                loginMsg.textContent = data.message || 'Login failed.';
            }
        })
        .catch(() => {
            document.getElementById('login-btn').disabled = false;
            loginMsg.style.color = 'red';
            loginMsg.textContent = 'Network error.';
        });
    };
}

// --- Registration (same fix for AJAX) ---
const registerForm = document.getElementById('register-form');
const registerMsg = document.getElementById('register-msg');
if (registerForm) {
    registerForm.onsubmit = function(e) {
        e.preventDefault();
        registerMsg.textContent = '';
        document.getElementById('register-btn').disabled = true;
        const password = document.getElementById('register-password').value;
        const confirm = document.getElementById('register-confirm').value;
        if (password !== confirm) {
            registerMsg.style.color = 'red';
            registerMsg.textContent = 'Passwords do not match.';
            document.getElementById('register-btn').disabled = false;
            return;
        }
        const formData = new FormData(registerForm);
        fetch('register.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('register-btn').disabled = false;
            if (data.success) {
                registerMsg.style.color = 'green';
                registerMsg.textContent = 'Registration successful! You can now login.';
                setTimeout(() => showPanel('login'), 1200);
            } else {
                registerMsg.style.color = 'red';
                registerMsg.textContent = data.message || 'Registration failed.';
            }
        })
        .catch(() => {
            document.getElementById('register-btn').disabled = false;
            registerMsg.style.color = 'red';
            registerMsg.textContent = 'Network error.';
        });
    };
}

// --- Movies Panel ---
document.getElementById('movie-search').oninput = function(e) {
  renderMovies(e.target.value);
};
function renderMovies(search = "") {
  const panel = document.getElementById("movies-panel");
  panel.innerHTML = "<h2>Now Showing</h2>";
  movies.forEach((m, i) => {
    if (search && !m.title.toLowerCase().includes(search.toLowerCase())) return;
    let seatInfo = "<ul>";
    cinemaTypes.forEach((cinema, cIdx) => {
      const seatsPerType = [50, 30, 10][cIdx];
      showTimes[i].forEach((st, sIdx) => {
        const key = `${i}-${cIdx}-${sIdx}`;
        seatInfo += `<li id="seat-info-${key}">${cinema} - ${st}: <b>Loading...</b></li>`;
        // Fetch booked seats for this movie/cinema/showtime
        fetch(`get_booked_seats.php?movie=${i}&cinema=${cIdx}&showtime=${sIdx}`)
          .then(res => res.json())
          .then(booked => {
            const available = seatsPerType - booked.length;
            const el = document.getElementById(`seat-info-${key}`);
            if (el) {
              el.innerHTML = `${cinema} - ${st}: <b>${available}</b> seats available`;
            }
          })
          .catch(() => {
            const el = document.getElementById(`seat-info-${key}`);
            if (el) {
              el.innerHTML = `${cinema} - ${st}: <b>Error</b>`;
            }
          });
      });
    });
    seatInfo += "</ul>";

    panel.innerHTML += `
      <div class="movie-card">
        <img src="${m.img}" alt="${m.title}" onerror="this.onerror=null;this.src='placeholder.jpg';this.alt='No Image';">
        <div>
          <h3>${m.title}</h3>
          <p><i>${m.desc}</i></p>
          <b>Available Seats:</b>
          ${seatInfo}
          <button onclick="startBooking(${i})">Book</button>
        </div>
      </div>
    `;
  });
}
function startBooking(movieIdx) {
  booking.movie = movieIdx;
  booking.cinema = 0;
  booking.showtime = 0;
  booking.tickets = 1;
  booking.seats = [];
  showPanel("book");
}

// --- Book Panel ---
function renderBook() {
  const m = movies[booking.movie];
  const panel = document.getElementById("book-panel");
  const cIdx = booking.cinema;
  const sIdx = booking.showtime;
  const seatsPerType = [50, 30, 10][cIdx];

  // Fetch booked seats from the server
  fetch(`get_booked_seats.php?movie=${booking.movie}&cinema=${cIdx}&showtime=${sIdx}`)
    .then(res => res.json())
    .then(booked => {
      const availableSeats = seatsPerType - booked.length;

      panel.innerHTML = `
        <h2>Book Your Ticket</h2>
        <label>Movie: <b>${m.title}</b></label>
        <label>Cinema Type:
          <select id="cinema-type">${cinemaTypes.map((c, i) => `<option value="${i}">${c}</option>`)}</select>
        </label>
        <label>Showtime:
          <select id="showtime">${showTimes[booking.movie].map((st, i) => `<option value="${i}">${st}</option>`)}</select>
        </label>
        <label>Tickets:
          <input type="number" id="ticket-count" min="1" max="${availableSeats}" value="${Math.min(booking.tickets, availableSeats)}">
          <span style="color:gray;">(Max: ${availableSeats} available)</span>
        </label>
        <div>
          <b>Select Seats:</b>
          <div id="seat-map"></div>
        </div>
        <div>
          <b>Total: ₱<span id="ticket-total"></span></b>
        </div>
        <button onclick="nextFood()">Next</button>
      `;
      document.getElementById("cinema-type").value = booking.cinema;
      document.getElementById("showtime").value = booking.showtime;
      document.getElementById("ticket-count").value = Math.min(booking.tickets, availableSeats);
      document.getElementById("cinema-type").onchange = e => { booking.cinema = +e.target.value; renderBook(); };
      document.getElementById("showtime").onchange = e => { booking.showtime = +e.target.value; renderBook(); };
      document.getElementById("ticket-count").onchange = e => { 
        booking.tickets = Math.min(+e.target.value, availableSeats); 
        booking.seats = []; 
        renderBook(); 
      };
      renderSeats();
      updateTicketTotal();
    });
}
function renderSeats() {
    const seatMap = document.getElementById("seat-map");
    const m = booking.movie, c = booking.cinema, s = booking.showtime;
    const totalSeats = [50, 30, 10][c];

    // Fetch booked seats from the server
    fetch(`get_booked_seats.php?movie=${m}&cinema=${c}&showtime=${s}`)
        .then(res => res.json())
        .then(booked => {
            seatMap.innerHTML = "";
            for (let i = 1; i <= totalSeats; i++) {
                const seatNum = "S" + i;
                const isBooked = booked.includes(seatNum);
                const isSelected = booking.seats.includes(seatNum);
                seatMap.innerHTML += `<span class="seat${isBooked ? " booked" : ""}${isSelected ? " selected" : ""}" onclick="selectSeat('${seatNum}', ${isBooked})">${i}</span>`;
            }
        });
}
function selectSeat(seatNum, isBooked) {
  if (isBooked) return;
  if (booking.seats.includes(seatNum)) {
    booking.seats = booking.seats.filter(s => s !== seatNum);
  } else if (booking.seats.length < booking.tickets) {
    booking.seats.push(seatNum);
  }
  renderSeats();
}
function updateTicketTotal() {
  const price = movies[booking.movie].price[booking.cinema];
  document.getElementById("ticket-total").innerText = price * booking.tickets;
}
function nextFood() {
    // Ensure the user selected exactly the number of tickets
    if (booking.seats.length !== booking.tickets) {
        alert("Please select exactly " + booking.tickets + " seat(s).");
        return;
    }

    // Ensure booking.food exists (reset if not)
    if (!Array.isArray(booking.food) || booking.food.length !== foodItems.length) {
        booking.food = new Array(foodItems.length).fill(0);
    }

    // Move to the food selection panel (do not save booking yet)
    showPanel("food");
    // Optionally render the food panel immediately
    renderFood();
}

// --- Food Panel ---
function renderFood() {
  const panel = document.getElementById("food-panel");
  panel.innerHTML = `<h2>Select your snacks and drinks:</h2>`;
  foodItems.forEach((f, i) => {
    panel.innerHTML += `
      <div class="food-row">
        <span>${f.name} (₱${f.price})</span>
        <button onclick="changeFood(${i}, -1)">-</button>
        <span id="food-qty-${i}">${booking.food[i]}</span>
        <button onclick="changeFood(${i}, 1)">+</button>
      </div>
    `;
  });
  panel.innerHTML += `<div><b>Food Total: ₱<span id="food-total"></span></b></div>`;
  panel.innerHTML += `<button onclick="nextPayment()">Next</button>`;
  updateFoodTotal();
}
function changeFood(idx, delta) {
  booking.food[idx] = Math.max(0, booking.food[idx] + delta);
  document.getElementById("food-qty-" + idx).innerText = booking.food[idx];
  updateFoodTotal();
}
function updateFoodTotal() {
  let total = 0;
  for (let i = 0; i < foodItems.length; i++) {
    total += booking.food[i] * foodItems[i].price;
  }
  document.getElementById("food-total").innerText = total;
}
function nextPayment() {
  showPanel("payment");
}

// --- Payment Panel ---
function renderPayment() {
  const panel = document.getElementById("payment-panel");
  const price = movies[booking.movie].price[booking.cinema];
  const ticketTotal = price * booking.tickets;
  let foodTotal = 0;
  let foodSummary = "";
  for (let i = 0; i < foodItems.length; i++) {
    if (booking.food[i] > 0) {
      foodSummary += `${foodItems[i].name} x${booking.food[i]} (₱${booking.food[i] * foodItems[i].price}) `;
      foodTotal += booking.food[i] * foodItems[i].price;
    }
  }
  let grandTotal = ticketTotal + foodTotal;
  panel.innerHTML = `
    <h2>Order Summary & Payment</h2>
    <div class="summary">
      <b>Movie:</b> ${movies[booking.movie].title}<br>
      <b>Cinema Type:</b> ${cinemaTypes[booking.cinema]}<br>
      <b>Showtime:</b> ${showTimes[booking.movie][booking.showtime]}<br>
      <b>Tickets:</b> ${booking.tickets} x ₱${price}<br>
      <b>Selected Seats:</b> ${booking.seats.join(", ")}<br>
      <b>Ticket Total:</b> ₱${ticketTotal}<br>
      <b>Food:</b> ${foodSummary || "None"}<br>
      <b>Food Total:</b> ₱${foodTotal}<br>
      <b>Grand Total:</b> ₱${grandTotal}<br>
      <label>Discount:
        <select id="discount">
          <option value="0">No Discount</option>
          <option value="0.10">Student (10%)</option>
          <option value="0.15">Senior (15%)</option>
          <option value="0.20">PWD (20%)</option>
        </select>
      </label>
      <div id="final-total"></div>
    </div>
    <button onclick="finishPayment()">Pay & Finish</button>
  `;
  document.getElementById("discount").onchange = updateFinalTotal;
  updateFinalTotal();
}
function updateFinalTotal() {
  const price = movies[booking.movie].price[booking.cinema];
  const ticketTotal = price * booking.tickets;
  let foodTotal = 0;
  for (let i = 0; i < foodItems.length; i++) {
    foodTotal += booking.food[i] * foodItems[i].price;
  }
  let grandTotal = ticketTotal + foodTotal;
  let discount = parseFloat(document.getElementById("discount").value);
  let discountAmt = Math.floor(grandTotal * discount);
  let finalTotal = grandTotal - discountAmt;
  document.getElementById("final-total").innerHTML =
    `<b>Discount:</b> ₱${discountAmt}<br><b>Final Total:</b> ₱${finalTotal}`;
}
function finishPayment() {
  const discount = parseFloat(document.getElementById("discount").value || 0);
  const ticketPrice = movies[booking.movie].price[booking.cinema];
  const ticketTotal = ticketPrice * booking.tickets;
  let foodTotal = 0;
  const selectedFoods = [];

  for (let i = 0; i < foodItems.length; i++) {
    const qty = booking.food[i];
    if (qty > 0) {
      foodTotal += qty * foodItems[i].price;
      selectedFoods.push({ name: foodItems[i].name, qty: qty });
    }
  }

  const grandTotal = ticketTotal + foodTotal;
  const discountAmt = Math.floor(grandTotal * discount);
  const finalTotal = grandTotal - discountAmt;

  const payload = {
    movie: movies[booking.movie].title,
    cinema: cinemaTypes[booking.cinema],
    showtime: showTimes[booking.movie][booking.showtime],
    tickets: booking.tickets,
    seats: booking.seats,
    foods: selectedFoods,
    discount: discount,
    finalTotal: finalTotal
  };

  fetch('save_booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      alert("Payment Successful! Ticket emailed.\nBooking ID: " + response.bookingId);
      booking = {
        movie: 0, cinema: 0, showtime: 0, tickets: 1, seats: [], food: [0, 0, 0, 0]
      };
      showPanel("movies");
    } else {
      alert("Booking failed: " + response.message);
    }
  })
  .catch(() => {
    alert("Network error during payment.");
  });
}


// --- Booking History Panel ---
function renderHistory() {
    fetch('get_user_bookings.php')
      .then(res => res.json())
      .then(bookings => {
        const panel = document.getElementById("history-panel");
        if (!bookings.length) {
          panel.innerHTML = "<h2>Your Booking History</h2><p>No bookings yet.</p>";
          return;
        }
        panel.innerHTML = "<h2>Your Booking History</h2><ul>" +
          bookings.map(b => `<li>${b.movie_id} - ${b.cinema_type} - ${b.showtime} - Seat: ${b.seat}</li>`).join("") +
          "</ul>";
      });
}
function setActiveNav(panel) {
    document.querySelectorAll('nav button').forEach(btn => btn.classList.remove('active'));
    const btn = Array.from(document.querySelectorAll('nav button')).find(b => b.textContent.toLowerCase().includes(panel));
    if (btn) btn.classList.add('active');
}
const oldShowPanel = window.showPanel;
window.showPanel = function(panel) {
    oldShowPanel(panel);
    setActiveNav(panel);
    if (panel === 'history') renderHistory();
};

