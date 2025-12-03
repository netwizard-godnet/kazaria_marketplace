buildscript {
    repositories {
        google()
        mavenCentral()
    }
    dependencies {
        // Plugin Android
        classpath("com.android.tools.build:gradle:8.5.0")

        // Plugin Google Services (Firebase) – version corrigée
        classpath("com.google.gms:google-services:4.4.4")

        // Plugin Kotlin (si besoin pour compatibilité)
        classpath(kotlin("gradle-plugin", version = "1.9.10"))
    }
}

allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}

subprojects {
    project.evaluationDependsOn(":app")
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
