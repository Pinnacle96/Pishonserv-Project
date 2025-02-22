<!-- Footer Section -->
<footer class="bg-[#020B34] text-white pt-16">
    <div class="container mx-auto py-4 px-6 md:px-10 lg:px-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- About Company -->
            <div>
                <h3 class="text-xl font-bold mb-4">About Us</h3>
                <p class="text-gray-300">Welcome to Pishon Serv, your premier destination for finding the perfect
                    property to rent or buy. Whether you’re looking for a short-term rental or a long-term investment,
                    we specialize in offering a diverse range of high-quality properties that cater to all your needs.

                    Quality Listings: Each property in our portfolio is vetted to meet our high standards of quality,
                    comfort, and aesthetic appeal.
                </p>
                <div class="flex space-x-4 mt-4">
                    <a href="#" class="text-white hover:text-[#CC9933] text-2xl"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white hover:text-[#CC9933] text-2xl"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="text-white hover:text-[#CC9933] text-2xl"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-white hover:text-[#CC9933] text-2xl"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-[#CC9933]">Home</a></li>
                    <li><a href="#" class="hover:text-[#CC9933]">Properties</a></li>
                    <li><a href="#" class="hover:text-[#CC9933]">Agents</a></li>
                    <li><a href="#" class="hover:text-[#CC9933]">Contact</a></li>
                </ul>
            </div>


            <!-- Contact Information -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <p class="text-gray-300"><i class="fas fa-map-marker-alt"></i> Nomadian Tech Hub 3rd Floor 152 Obafemi
                    Awolowo way, opposite Airport hotel near Allen Junction Bus Stop. IKeja</p>
                <p class="text-gray-300"><i class="fas fa-envelope"></i> inquiry@pishonserv.com</p>
                <p class="text-gray-300"><i class="fas fa-phone"></i>+2348111973369</p>
            </div>

            <!-- Newsletter Subscription (Moved Here) -->
            <div>
                <h3 class="text-xl font-bold mb-4">Newsletter</h3>
                <p class="text-gray-300 mb-4">Subscribe for the latest real estate updates.</p>
                <form action="#" method="POST" class="flex flex-col space-y-3">
                    <input type="email" name="email" class="p-3 rounded text-gray-800" placeholder="Enter your email"
                        required>
                    <button type="submit"
                        class="bg-[#CC9933] text-white px-6 py-3 rounded hover:bg-[#d88b1c]">Subscribe</button>
                </form>
            </div>

        </div>


        <!-- Copyright -->
        <div class="mt-10 py-6 text-center text-gray-400 border-t border-gray-500 pt-6">
            <p>&copy; <?php echo date('Y'); ?> Pishonserv. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- FontAwesome for Icons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
<script src="<?php echo $base_path; ?>public/js/slider.js"></script>
<script src="<?php echo $base_path; ?>public/js/script.js"></script>
<script src="<?php echo $base_path; ?>public/js/navbar.js"></script>
<script src="<?php echo $base_path; ?>public/js/search.js"></script>
<script src="<?php echo $base_path; ?>public/js/testimonials.js"></script>