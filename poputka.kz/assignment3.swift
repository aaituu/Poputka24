//------- Part 1: Arrays -------
// ----1
var movieTitles = ["Good boy", "Dastur 1", "N37", "Auru", "Qazaq alemi"]

// ----2
print("First movie:", movieTitles.first ?? "No movie")
print("Last movie:", movieTitles.last ?? "No movie")

// -----3
movieTitles.append("Dastur 2:  Teris bata")
// ----4
movieTitles.remove(at: 2) 

// ----5
for (index, title) in movieTitles.enumerated() {
    print("\(index + 1). \(title)")
}


//------- Part 2: Sets -------
// ----1
var heroes: Set = ["Iron Man", "Spider-Man", "Thor", "Hulk", "Capitan America"]
var villains: Set = ["Thanos", "Loki", "Green Goblin", "Hela", "Magneto"]

// ----2
print("Union:", heroes.union(villains))
print("Intersection:", heroes.intersection(villains))
print("Difference (heroes - villains):", heroes.subtracting(villains))

// ----3
print("Contains Thor?", heroes.contains("Thor"))

// -----4
heroes.insert("Doctor Strange")
heroes.remove("Hulk")



//------- Part 3: Dictionaries -------
// ----1
var powerLevel = ["Jojo": 100, "Luffy": 9000, "Naruto": 5000]

// ----2
print("Luffy's power level:", powerLevel["Luffy"] ?? 0)

// ----3
powerLevel["Sasuke"] = 4500

// ----4
powerLevel["Jojo"] = 120

// ----5
powerLevel.removeValue(forKey: "Naruto")

// ----6
for (name, power) in powerLevel {
    print("\(name): \(power)")
}

//------- Part 4: Nested Collections -------
// ----1
var teams: [String: [String]] = [
    "TeamA": ["Balgyn", "Ayana", "Adil"],
    "TeamB": ["Merei", "Dias"]
]

//----2
print("Members of TeamA:", teams["TeamA"] ?? [])

//-----3
teams["TeamA"]?.append("Jasmin")

// -----4
teams["TeamC"] = ["Nurai", "Ayaulym"]

// -----5
for (team, members) in teams {
    print("\(team): \(members.joined(separator: ", "))")
}


//------- Part 5: Practical Challenge — Battle Game -------

//----1
var heroesArray = ["Iron Man", "Thor", "Capitan America", "Doctor Strange"]

// --------2
var heroPower: [String: Int] = ["Iron Man": 90, "Thor": 95, "Capitan America": 70, "Doctor Strange": 100]

//---------3
heroesArray.append("Hulk")
heroPower["Hulk"] = 120

// ---------4
if let strongest = heroPower.max(by: { $0.value < $1.value }) {
    print("Strongest hero: \(strongest.key) with power \(strongest.value)")
}

// ----------5
let activeHeroes: Set = ["Thor", "Hulk", "Black Widow"]
let currentHeroes = Set(heroesArray)
let participatingHeroes = activeHeroes.intersection(currentHeroes)
print("Participating heroes:", participatingHeroes)

// ---------6
for hero in heroesArray {
    let status = participatingHeroes.contains(hero) ? "Ready" : "Not ready"
    print("\(hero): \(status)")
}