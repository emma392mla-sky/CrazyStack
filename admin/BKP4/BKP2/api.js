const crazy = {
    url: "https://awnzbiatwnfmryerfxwg.supabase.co",
    key: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg",

    getHeaders: function() {
        return {
            "apikey": crazy.key,
            "Authorization": "Bearer " + crazy.key,
            "Content-Type": "application/json"
        };
    },

    balance: async function(phone) {
        try {
            // CHANGED: this.url -> crazy.url
            const r = await fetch(`${crazy.url}/rest/v1/users?phone=eq.${phone}&select=balance`, {
                method: "GET",
                headers: crazy.getHeaders()
            });
            const d = await r.json();
            return (d && d.length > 0) ? d[0].balance : "User not found";
        } catch (e) {
            return "Error: " + e.message;
        }
    },

    withdraw: async function(phone, amount) {
        try {
            // CHANGED: this.url -> crazy.url
            const r = await fetch(`${crazy.url}/functions/v1/cashout`, {
                method: "POST",
                headers: crazy.getHeaders(),
                body: JSON.stringify({ mobile: phone, amount: Number(amount) })
            });
            const d = await r.json();
            return d.status || "No status returned";
        } catch (e) {
            return "Error: " + e.message;
        }
    }
};