function modify(val, ...args) {
    document.getElementById("modositas_form").style.display = "block";
    if (typeof val === "object") {
        let n = 0;
        for (const v in val) {
            for (let i = 0; i < 2; i++) {
                document.getElementById(args[n++]).value = val[v];
            }
        }
    } else {
        for (let i = 0; i < args.length; i++) {
            if (typeof args[i] === "object") {
                for (let j = 0; j < args[i].length; j++) {
                    document.getElementById(args[i][j]).value = val;
                }
            } else {
                document.getElementById(args[i]).value = val;
            }
        }
    }
}

function closeModificationForm() {
    document.getElementById("modositas_form").style.display = "none";
}